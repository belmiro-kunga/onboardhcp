<?php

namespace App\Services;

use App\Models\Video;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use FFMpeg\FFMpeg;
use FFMpeg\FFProbe;
use FFMpeg\Format\Video\X264;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg as FFmpegWrapper;

class VideoProcessingService
{
    /**
     * Extract video ID from various platform URLs.
     *
     * @param string $url
     * @param string $sourceType
     * @return string|null
     */
    public function extractVideoId(string $url, string $sourceType): ?string
    {
        switch ($sourceType) {
            case 'youtube':
                return $this->extractYoutubeId($url);
            case 'vimeo':
                return $this->extractVimeoId($url);
            default:
                return null;
        }
    }

    /**
     * Extract YouTube video ID from URL.
     *
     * @param string $url
     * @return string|null
     */
    protected function extractYoutubeId(string $url): ?string
    {
        $pattern = '/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/';
        
        if (preg_match($pattern, $url, $matches)) {
            return $matches[1];
        }
        
        return null;
    }

    /**
     * Extract Vimeo video ID from URL.
     *
     * @param string $url
     * @return string|null
     */
    protected function extractVimeoId(string $url): ?string
    {
        $pattern = '/vimeo\.com\/(?:video\/)?(\d+)/';
        
        if (preg_match($pattern, $url, $matches)) {
            return $matches[1];
        }
        
        return null;
    }

    /**
     * Generate embed URL for various platforms.
     *
     * @param string $url
     * @param string $sourceType
     * @return string
     */
    public function generateEmbedUrl(string $url, string $sourceType): string
    {
        $videoId = $this->extractVideoId($url, $sourceType);
        
        if (!$videoId) {
            return $url;
        }
        
        switch ($sourceType) {
            case 'youtube':
                return "https://www.youtube.com/embed/{$videoId}";
            case 'vimeo':
                return "https://player.vimeo.com/video/{$videoId}";
            default:
                return $url;
        }
    }

    /**
     * Refresh video metadata from source.
     *
     * @param \App\Models\Video $video
     * @return void
     */
    public function refreshVideoMetadata(Video $video): void
    {
        if ($video->source_type === 'youtube') {
            $this->refreshYoutubeMetadata($video);
        } elseif ($video->source_type === 'vimeo') {
            $this->refreshVimeoMetadata($video);
        } elseif ($video->source_type === 'upload' && $video->file_path) {
            $this->refreshLocalVideoMetadata($video);
        }
    }

    /**
     * Refresh YouTube video metadata.
     *
     * @param \App\Models\Video $video
     * @return void
     */
    protected function refreshYoutubeMetadata(Video $video): void
    {
        $apiKey = config('services.youtube.api_key');
        
        if (!$apiKey) {
            return;
        }
        
        $response = Http::get('https://www.googleapis.com/youtube/v3/videos', [
            'id' => $video->external_id,
            'part' => 'snippet,contentDetails',
            'key' => $apiKey,
        ]);
        
        $data = $response->json();
        
        if (empty($data['items'])) {
            return;
        }
        
        $snippet = $data['items'][0]['snippet'];
        $contentDetails = $data['items'][0]['contentDetails'];
        
        // Update video metadata
        $video->update([
            'title' => $snippet['title'] ?? $video->title,
            'description' => $snippet['description'] ?? $video->description,
            'duration' => $this->parseDuration($contentDetails['duration'] ?? 0),
            'thumbnail_path' => $this->getBestThumbnail($snippet['thumbnails'] ?? []),
            'metadata' => array_merge($video->metadata ?? [], [
                'youtube' => [
                    'channel_id' => $snippet['channelId'] ?? null,
                    'channel_title' => $snippet['channelTitle'] ?? null,
                    'published_at' => $snippet['publishedAt'] ?? null,
                    'tags' => $snippet['tags'] ?? [],
                    'category_id' => $snippet['categoryId'] ?? null,
                    'live_broadcast_content' => $snippet['liveBroadcastContent'] ?? null,
                    'default_language' => $snippet['defaultLanguage'] ?? null,
                    'default_audio_language' => $snippet['defaultAudioLanguage'] ?? null,
                ],
            ]),
        ]);
    }

    /**
     * Refresh Vimeo video metadata.
     *
     * @param \App\Models\Video $video
     * @return void
     */
    protected function refreshVimeoMetadata(Video $video): void
    {
        $token = config('services.vimeo.access_token');
        
        if (!$token) {
            return;
        }
        
        $response = Http::withToken($token)
            ->get("https://api.vimeo.com/videos/{$video->external_id}");
        
        if (!$response->successful()) {
            return;
        }
        
        $data = $response->json();
        
        // Update video metadata
        $video->update([
            'title' => $data['name'] ?? $video->title,
            'description' => $data['description'] ?? $video->description,
            'duration' => $data['duration'] ?? $video->duration,
            'thumbnail_path' => $this->getBestVimeoThumbnail($data['pictures'] ?? []),
            'metadata' => array_merge($video->metadata ?? [], [
                'vimeo' => [
                    'user_id' => $data['user']['uri'] ?? null,
                    'user_name' => $data['user']['name'] ?? null,
                    'user_url' => $data['user']['link'] ?? null,
                    'width' => $data['width'] ?? null,
                    'height' => $data['height'] ?? null,
                    'embed_html' => $data['embed']['html'] ?? null,
                    'language' => $data['language'] ?? null,
                    'created_time' => $data['created_time'] ?? null,
                    'modified_time' => $data['modified_time'] ?? null,
                    'release_time' => $data['release_time'] ?? null,
                    'content_rating' => $data['content_rating'] ?? [],
                    'license' => $data['license'] ?? null,
                    'privacy' => $data['privacy'] ?? [],
                    'pictures' => $data['pictures'] ?? [],
                    'stats' => $data['stats'] ?? [],
                ],
            ]),
        ]);
    }

    /**
     * Refresh local video metadata using FFmpeg.
     *
     * @param \App\Models\Video $video
     * @return void
     */
    protected function refreshLocalVideoMetadata(Video $video): void
    {
        try {
            $ffprobe = FFProbe::create();
            $file = Storage::path($video->file_path);
            
            if (!file_exists($file)) {
                return;
            }
            
            $streams = $ffprobe->streams($file);
            $videoStream = $streams->videos()->first();
            $audioStream = $streams->audios()->first();
            $format = $ffprobe->format($file);
            
            // Generate thumbnail if not exists
            $thumbnailPath = $this->generateThumbnail($video, $file);
            
            $metadata = array_merge($video->metadata ?? [], [
                'format' => [
                    'filename' => basename($file),
                    'size' => $format->get('size'),
                    'bit_rate' => $format->get('bit_rate'),
                    'duration' => $format->get('duration'),
                    'format_name' => $format->get('format_name'),
                    'format_long_name' => $format->get('format_long_name'),
                ],
                'video' => $videoStream ? [
                    'codec_name' => $videoStream->get('codec_name'),
                    'codec_long_name' => $videoStream->get('codec_long_name'),
                    'width' => $videoStream->get('width'),
                    'height' => $videoStream->get('height'),
                    'display_aspect_ratio' => $videoStream->get('display_aspect_ratio'),
                    'pix_fmt' => $videoStream->get('pix_fmt'),
                    'r_frame_rate' => $videoStream->get('r_frame_rate'),
                    'avg_frame_rate' => $videoStream->get('avg_frame_rate'),
                    'bit_rate' => $videoStream->get('bit_rate'),
                ] : null,
                'audio' => $audioStream ? [
                    'codec_name' => $audioStream->get('codec_name'),
                    'codec_long_name' => $audioStream->get('codec_long_name'),
                    'sample_rate' => $audioStream->get('sample_rate'),
                    'channels' => $audioStream->get('channels'),
                    'channel_layout' => $audioStream->get('channel_layout'),
                    'bit_rate' => $audioStream->get('bit_rate'),
                ] : null,
            ]);
            
            // Update video metadata
            $video->update([
                'duration' => (int) $format->get('duration', $video->duration),
                'file_size' => $format->get('size', $video->file_size),
                'mime_type' => mime_content_type($file) ?: $video->mime_type,
                'thumbnail_path' => $thumbnailPath ?: $video->thumbnail_path,
                'metadata' => $metadata,
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Failed to refresh local video metadata', [
                'video_id' => $video->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Generate a thumbnail from a video file.
     *
     * @param \App\Models\Video $video
     * @param string $videoPath
     * @return string|null
     */
    protected function generateThumbnail(Video $video, string $videoPath): ?string
    {
        try {
            $thumbnailDir = 'public/thumbnails/' . now()->format('Y/m');
            Storage::makeDirectory($thumbnailDir);
            
            $filename = Str::random(40) . '.jpg';
            $thumbnailPath = $thumbnailDir . '/' . $filename;
            $fullThumbnailPath = Storage::path($thumbnailPath);
            
            // Use FFmpeg to generate thumbnail at 10% of the video
            $ffmpeg = FFMpeg::create([
                'ffmpeg.binaries' => config('ffmpeg.ffmpeg_path', '/usr/bin/ffmpeg'),
                'ffprobe.binaries' => config('ffmpeg.ffprobe_path', '/usr/bin/ffprobe'),
                'timeout' => 3600,
                'ffmpeg.threads' => 12,
            ]);
            
            $videoFF = $ffmpeg->open($videoPath);
            $duration = $videoFF->getStreams()->videos()->first()->get('duration');
            $timecode = $duration * 0.1; // 10% of the video
            
            $frame = $videoFF->frame(FFMpeg\Coordinate\TimeCode::fromSeconds($timecode));
            $frame->save($fullThumbnailPath);
            
            return str_replace('public/', '', $thumbnailPath);
            
        } catch (\Exception $e) {
            \Log::error('Failed to generate thumbnail', [
                'video_id' => $video->id,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Clean up video files when a video is deleted.
     *
     * @param \App\Models\Video $video
     * @return void
     */
    public function cleanupVideoFiles(Video $video): void
    {
        // Delete any generated HLS/DASH files
        if ($video->hls_playlist_path) {
            Storage::delete($video->hls_playlist_path);
            $this->deleteDirectory(dirname($video->hls_playlist_path));
        }
        
        if ($video->dash_manifest_path) {
            Storage::delete($video->dash_manifest_path);
            $this->deleteDirectory(dirname($video->dash_manifest_path));
        }
        
        // Delete any generated previews
        if ($video->preview_path) {
            Storage::delete($video->preview_path);
        }
        
        // Delete any generated thumbnails
        if ($video->thumbnail_path) {
            Storage::delete($video->thumbnail_path);
        }
        
        // Delete subtitles
        if (!empty($video->subtitles)) {
            foreach ($video->subtitles as $subtitle) {
                if (isset($subtitle['path'])) {
                    Storage::delete($subtitle['path']);
                }
            }
        }
    }

    /**
     * Parse ISO 8601 duration to seconds.
     *
     * @param string $duration
     * @return int
     */
    protected function parseDuration(string $duration): int
    {
        $interval = new \DateInterval($duration);
        return ($interval->d * 24 * 60 * 60) +
               ($interval->h * 60 * 60) +
               ($interval->i * 60) +
               $interval->s;
    }

    /**
     * Get the best quality thumbnail from YouTube thumbnails.
     *
     * @param array $thumbnails
     * @return string|null
     */
    protected function getBestThumbnail(array $thumbnails): ?string
    {
        $preferredOrder = ['maxres', 'standard', 'high', 'medium', 'default'];
        
        foreach ($preferredOrder as $quality) {
            if (isset($thumbnails[$quality]['url'])) {
                return $thumbnails[$quality]['url'];
            }
        }
        
        return $thumbnails[array_key_first($thumbnails)]['url'] ?? null;
    }

    /**
     * Get the best quality thumbnail from Vimeo thumbnails.
     *
     * @param array $pictures
     * @return string|null
     */
    protected function getBestVimeoThumbnail(array $pictures): ?string
    {
        if (empty($pictures['sizes'])) {
            return null;
        }
        
        // Sort by width in descending order
        usort($pictures['sizes'], function ($a, $b) {
            return $b['width'] <=> $a['width'];
        });
        
        return $pictures['sizes'][0]['link'] ?? null;
    }

    /**
     * Recursively delete a directory.
     *
     * @param string $path
     * @return bool
     */
    protected function deleteDirectory(string $path): bool
    {
        if (!Storage::exists($path)) {
            return true;
        }
        
        $files = Storage::allFiles($path);
        $directories = Storage::allDirectories($path);
        
        // Delete all files in the directory
        foreach ($files as $file) {
            Storage::delete($file);
        }
        
        // Recursively delete all subdirectories
        foreach ($directories as $directory) {
            $this->deleteDirectory($directory);
        }
        
        // Delete the directory itself if empty
        return Storage::deleteDirectory($path);
    }
}
