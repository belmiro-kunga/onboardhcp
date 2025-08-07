<?php

namespace App\Jobs;

use App\Models\Video;
use App\Services\VideoProcessingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use FFMpeg\FFMpeg;
use FFMpeg\Format\Video\X264;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg as FFmpegWrapper;

class ProcessVideoUpload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The video instance.
     *
     * @var \App\Models\Video
     */
    protected $video;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The maximum number of unhandled exceptions to allow before failing.
     *
     * @var int
     */
    public $maxExceptions = 3;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 3600; // 1 hour

    /**
     * Create a new job instance.
     *
     * @param  \App\Models\Video  $video
     * @return void
     */
    public function __construct(Video $video)
    {
        $this->video = $video->withoutRelations();
    }

    /**
     * Execute the job.
     *
     * @param  \App\Services\VideoProcessingService  $videoService
     * @return void
     */
    public function handle(VideoProcessingService $videoService)
    {
        // Reload the video to ensure we have the latest data
        $video = Video::findOrFail($this->video->id);
        
        // Skip if not an uploaded video or file doesn't exist
        if ($video->source_type !== 'upload' || !$video->file_path) {
            Log::warning('Skipping video processing - invalid source type or missing file', [
                'video_id' => $video->id,
                'source_type' => $video->source_type,
                'file_path' => $video->file_path,
            ]);
            return;
        }
        
        try {
            // Update video status to processing
            $video->update(['processing_status' => 'processing']);
            
            // Get the full path to the video file
            $videoPath = storage_path('app/' . $video->file_path);
            
            if (!file_exists($videoPath)) {
                throw new \Exception("Video file not found at path: {$videoPath}");
            }
            
            // Generate HLS and DASH streams
            $this->generateStreams($video);
            
            // Generate preview (first 30 seconds)
            $previewPath = $this->generatePreview($video);
            
            // Generate waveform if audio is present
            $waveformPath = $this->generateWaveform($video);
            
            // Update video with processed files and metadata
            $video->update([
                'processing_status' => 'completed',
                'preview_path' => $previewPath,
                'waveform_path' => $waveformPath,
                'processed_at' => now(),
            ]);
            
            Log::info('Video processing completed successfully', ['video_id' => $video->id]);
            
        } catch (\Exception $e) {
            Log::error('Video processing failed', [
                'video_id' => $video->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            // Update video status to failed
            $video->update([
                'processing_status' => 'failed',
                'processing_error' => $e->getMessage(),
            ]);
            
            // Re-throw to allow job retries
            throw $e;
        }
    }
    
    /**
     * Generate HLS and DASH streams for the video.
     *
     * @param  \App\Models\Video  $video
     * @return void
     */
    protected function generateStreams(Video $video)
    {
        $videoPath = storage_path('app/' . $video->file_path);
        $outputDir = 'processed_videos/' . $video->id;
        $hlsPlaylistPath = $outputDir . '/hls/playlist.m3u8';
        $dashManifestPath = $outputDir . '/dash/manifest.mpd';
        
        // Create output directories
        Storage::makeDirectory($outputDir . '/hls');
        Storage::makeDirectory($outputDir . '/dash');
        
        // Initialize FFmpeg
        $ffmpeg = FFMpeg::create([
            'ffmpeg.binaries' => config('ffmpeg.ffmpeg_path', '/usr/bin/ffmpeg'),
            'ffprobe.binaries' => config('ffmpeg.ffprobe_path', '/usr/bin/ffprobe'),
            'timeout' => 3600,
            'ffmpeg.threads' => 12,
        ]);
        
        // Open the video file
        $videoFF = $ffmpeg->open($videoPath);
        
        // Get video stream information
        $videoStream = $videoFF->getStreams()->videos()->first();
        $width = $videoStream->get('width');
        $height = $videoStream->get('height');
        
        // Define output formats
        $hlsFormat = new X264('aac');
        $hlsFormat->setAudioCodec('aac');
        
        // Generate HLS streams
        $hlsStreams = [
            ['width' => 426, 'height' => 240, 'bitrate' => 400],
            ['width' => 640, 'height' => 360, 'bitrate' => 800],
            ['width' => 854, 'height' => 480, 'bitrate' => 1400],
            ['width' => 1280, 'height' => 720, 'bitrate' => 2800],
            ['width' => 1920, 'height' => 1080, 'bitrate' => 5000],
        ];
        
        $hlsStreams = array_filter($hlsStreams, function($stream) use ($width, $height) {
            return $stream['width'] <= $width && $stream['height'] <= $height;
        });
        
        // Sort by resolution (low to high)
        usort($hlsStreams, function($a, $b) {
            return ($a['width'] * $a['height']) - ($b['width'] * $b['height']);
        });
        
        // Generate HLS master playlist
        $hlsPlaylist = "#EXTM3U\n";
        
        foreach ($hlsStreams as $index => $stream) {
            $streamName = "{$stream['height']}p";
            $bandwidth = $stream['bitrate'] * 1000;
            $resolution = "{$stream['width']}x{$stream['height']}";
            
            $hlsPlaylist .= "#EXT-X-STREAM-INF:BANDWIDTH={$bandwidth},RESOLUTION={$resolution}\n";
            $hlsPlaylist .= "{$streamName}/playlist.m3u8\n";
            
            // Generate HLS variant
            $hlsFormat->setKiloBitrate($stream['bitrate']);
            
            // Create directory for this variant
            $variantDir = $outputDir . '/hls/' . $streamName;
            Storage::makeDirectory($variantDir);
            
            // Generate HLS segments
            $videoFF->filters()
                ->resize(new \FFMpeg\Coordinate\Dimension($stream['width'], $stream['height']))
                ->synchronize();
                
            $videoFF->save($hlsFormat, storage_path('app/' . $variantDir . '/playlist.m3u8'));
        }
        
        // Save HLS master playlist
        Storage::put($hlsPlaylistPath, $hlsPlaylist);
        
        // Generate DASH manifest (simplified example)
        $dashManifest = '<?xml version="1.0" encoding="UTF-8"?>
<MPD xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
     xmlns="urn:mpeg:dash:schema:mpd:2011"
     xsi:schemaLocation="urn:mpeg:dash:schema:mpd:2011 DASH-MPD.xsd"
     type="static"
     mediaPresentationDuration="PT' . gmdate('H\h i\m s\s', $video->duration) . '"
     minBufferTime="PT1.5S"
     profiles="urn:mpeg:dash:profile:isoff-on-demand:2011">
    <Period>
        <AdaptationSet segmentAlignment="true" maxWidth="' . $width . '" maxHeight="' . $height . '" maxFrameRate="30" subsegmentAlignment="true" subsegmentStartsWithSAP="1">
            <Representation id="1" mimeType="video/mp4" codecs="avc1.64001f" width="' . $width . '" height="' . $height . '" frameRate="30" startWithSAP="1" bandwidth="' . (5000 * 1000) . '">
                <BaseURL>video.mp4</BaseURL>
                <SegmentBase>
                    <Initialization sourceURL="init.mp4"/>
                </SegmentBase>
            </Representation>
        </AdaptationSet>
    </Period>
</MPD>';
        
        // Save DASH manifest
        Storage::put($dashManifestPath, $dashManifest);
        
        // Update video with stream paths
        $video->update([
            'hls_playlist_path' => $hlsPlaylistPath,
            'dash_manifest_path' => $dashManifestPath,
        ]);
    }
    
    /**
     * Generate a preview clip (first 30 seconds) of the video.
     *
     * @param  \App\Models\Video  $video
     * @return string|null
     */
    protected function generatePreview(Video $video)
    {
        $videoPath = storage_path('app/' . $video->file_path);
        $previewPath = 'previews/' . $video->id . '/preview.mp4';
        
        // Create directory for preview
        Storage::makeDirectory(dirname($previewPath));
        
        // Initialize FFmpeg
        $ffmpeg = FFMpeg::create([
            'ffmpeg.binaries' => config('ffmpeg.ffmpeg_path', '/usr/bin/ffmpeg'),
            'ffprobe.binaries' => config('ffmpeg.ffprobe_path', '/usr/bin/ffprobe'),
        ]);
        
        // Open the video file
        $videoFF = $ffmpeg->open($videoPath);
        
        // Get video duration
        $duration = $videoFF->getStreams()->videos()->first()->get('duration');
        $previewDuration = min(30, $duration); // Max 30 seconds
        
        // Create a clip of the first 30 seconds (or less if video is shorter)
        $clip = $videoFF->clip(
            \FFMpeg\Coordinate\TimeCode::fromSeconds(0),
            \FFMpeg\Coordinate\TimeCode::fromSeconds($previewDuration)
        );
        
        // Save the preview
        $format = new X264('aac');
        $format->setAudioCodec('aac');
        
        $clip->save($format, storage_path('app/' . $previewPath));
        
        return $previewPath;
    }
    
    /**
     * Generate a waveform image for the video's audio.
     *
     * @param  \App\Models\Video  $video
     * @return string|null
     */
    protected function generateWaveform(Video $video)
    {
        $videoPath = storage_path('app/' . $video->file_path);
        $waveformPath = 'waveforms/' . $video->id . '.png';
        
        // Create directory for waveform
        Storage::makeDirectory(dirname($waveformPath));
        
        // Generate waveform using FFmpeg
        $ffmpeg = FFMpeg::create([
            'ffmpeg.binaries' => config('ffmpeg.ffmpeg_path', '/usr/bin/ffmpeg'),
            'ffprobe.binaries' => config('ffmpeg.ffprobe_path', '/usr/bin/ffprobe'),
        ]);
        
        // Generate waveform as PNG
        $command = sprintf(
            '%s -i %s -filter_complex "aformat=channel_layouts=mono,compand,showwavespic=split_channels=1:s=1200x120:colors=#007bff|#0056b3:scale=0" -frames:v 1 %s',
            config('ffmpeg.ffmpeg_path', 'ffmpeg'),
            escapeshellarg($videoPath),
            storage_path('app/' . $waveformPath)
        );
        
        exec($command, $output, $returnCode);
        
        if ($returnCode !== 0) {
            Log::warning('Failed to generate waveform', [
                'video_id' => $video->id,
                'command' => $command,
                'output' => $output,
                'return_code' => $returnCode,
            ]);
            return null;
        }
        
        return $waveformPath;
    }
    
    /**
     * Handle a job failure.
     *
     * @param  \Throwable  $exception
     * @return void
     */
    public function failed(\Throwable $exception)
    {
        $video = Video::find($this->video->id);
        
        if ($video) {
            $video->update([
                'processing_status' => 'failed',
                'processing_error' => $exception->getMessage(),
            ]);
        }
        
        Log::error('Video processing job failed', [
            'video_id' => $this->video->id,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
