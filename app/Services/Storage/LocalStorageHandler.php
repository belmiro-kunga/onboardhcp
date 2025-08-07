<?php

namespace App\Services\Storage;

use App\Contracts\StorageHandlerInterface;
use FFMpeg\FFMpeg;
use FFMpeg\Coordinate\TimeCode;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;
use Symfony\Component\Process\ExecutableFinder;

class LocalStorageHandler implements StorageHandlerInterface
{
    private const ALLOWED_EXTENSIONS = ['mp4', 'avi', 'mov', 'wmv', 'flv', 'webm', 'mkv'];
    private const MAX_FILE_SIZE = 500 * 1024 * 1024; // 500MB
    private const THUMBNAIL_WIDTH = 320;
    private const THUMBNAIL_HEIGHT = 180;

    public function store(UploadedFile $file, array $options = []): array
    {
        try {
            // Validar arquivo
            if (!$this->validate($file)) {
                throw new Exception('Arquivo inválido ou não suportado.');
            }

            // Gerar nome único
            $filename = $this->generateUniqueFilename($file);
            $directory = $options['directory'] ?? 'videos';
            $path = $directory . '/' . $filename;

            // Armazenar arquivo
            $storedPath = Storage::disk('public')->putFileAs($directory, $file, $filename);

            if (!$storedPath) {
                throw new Exception('Falha ao armazenar arquivo.');
            }

            // Extrair metadados
            $metadata = $this->extractMetadata(Storage::disk('public')->path($storedPath));

            // Gerar thumbnail
            $thumbnailPath = $this->generateThumbnail(Storage::disk('public')->path($storedPath), [
                'output_directory' => 'thumbnails',
                'filename' => pathinfo($filename, PATHINFO_FILENAME) . '.jpg'
            ]);

            // Comprimir vídeo se necessário
            $compressedPath = $this->compressVideo(Storage::disk('public')->path($storedPath), $options);

            Log::info('Arquivo armazenado localmente', [
                'original_name' => $file->getClientOriginalName(),
                'stored_path' => $storedPath,
                'size' => $file->getSize(),
                'duration' => $metadata['duration'] ?? null
            ]);

            return [
                'success' => true,
                'path' => $storedPath,
                'url' => Storage::disk('public')->url($storedPath),
                'thumbnail' => $thumbnailPath ? Storage::disk('public')->url($thumbnailPath) : null,
                'metadata' => $metadata,
                'compressed_path' => $compressedPath,
                'size' => $file->getSize(),
                'original_name' => $file->getClientOriginalName()
            ];

        } catch (Exception $e) {
            Log::error('Erro ao armazenar arquivo local', [
                'message' => $e->getMessage(),
                'file' => $file->getClientOriginalName()
            ]);
            throw $e;
        }
    }

    public function extractMetadata(string $filePath): array
    {
        try {
            if (!file_exists($filePath)) {
                return [];
            }

            $metadata = [
                'file_size' => filesize($filePath),
                'mime_type' => mime_content_type($filePath),
                'created_at' => date('Y-m-d H:i:s', filectime($filePath))
            ];

            // Usar FFMpeg para extrair informações de vídeo
            if (class_exists('\\FFMpeg\\FFMpeg')) {
                try {
                    $ffmpeg = \FFMpeg\FFMpeg::create();
                    $video = $ffmpeg->open($filePath);
                    $format = $video->getFormat();

                    $metadata['duration'] = $format->get('duration');
                    $metadata['bitrate'] = $format->get('bit_rate');
                    $metadata['width'] = $format->get('width');
                    $metadata['height'] = $format->get('height');
                    $metadata['codec'] = $format->get('codec_name');
                    $metadata['frame_rate'] = $format->get('r_frame_rate');
                } catch (Exception $e) {
                    Log::warning('Erro ao extrair metadados com FFMpeg', [
                        'file' => $filePath,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            return $metadata;

        } catch (Exception $e) {
            Log::error('Erro ao extrair metadados', [
                'file' => $filePath,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    public function generateThumbnail(string $filePath, array $options = []): ?string
    {
        try {
            if (!file_exists($filePath) || !class_exists('\\FFMpeg\\FFMpeg')) {
                return null;
            }

            $ffmpeg = \FFMpeg\FFMpeg::create();
            $video = $ffmpeg->open($filePath);

            $outputDirectory = $options['output_directory'] ?? 'thumbnails';
            $filename = $options['filename'] ?? Str::uuid() . '.jpg';
            $outputPath = $outputDirectory . '/' . $filename;

            // Criar diretório se não existir
            Storage::disk('public')->makeDirectory($outputDirectory);

            $frame = $video->frame(\FFMpeg\Coordinate\TimeCode::fromSeconds(1));
            $frame->save(Storage::disk('public')->path($outputPath));

            Log::info('Thumbnail gerado', [
                'source' => $filePath,
                'thumbnail' => $outputPath
            ]);

            return $outputPath;

        } catch (Exception $e) {
            Log::error('Erro ao gerar thumbnail', [
                'file' => $filePath,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    public function validate($source): bool
    {
        try {
            if (!$source instanceof UploadedFile || !$source->isValid()) {
                Log::warning('Arquivo inválido ou corrompido', [
                    'file' => $source->getClientOriginalName()
                ]);
                return false;
            }

            // Verificar extensão
            $extension = strtolower($source->getClientOriginalExtension());
            if (!in_array($extension, self::ALLOWED_EXTENSIONS)) {
                Log::warning('Extensão de arquivo não permitida', [
                    'file' => $source->getClientOriginalName(),
                    'extension' => $extension,
                    'allowed_extensions' => self::ALLOWED_EXTENSIONS
                ]);
                return false;
            }

            // Verificar tamanho
            $fileSize = $source->getSize();
            if ($fileSize === false || $fileSize > self::MAX_FILE_SIZE) {
                Log::warning('Tamanho de arquivo excede o limite permitido', [
                    'file' => $source->getClientOriginalName(),
                    'size' => $fileSize,
                    'max_size' => self::MAX_FILE_SIZE
                ]);
                return false;
            }

            // Verificar MIME type
            $mimeType = $source->getMimeType();
            if ($mimeType === null || substr($mimeType, 0, 6) !== 'video/') {
                Log::warning('Tipo MIME não suportado', [
                    'file' => $source->getClientOriginalName(),
                    'mime_type' => $mimeType
                ]);
                return false;
            }

            return true;
        } catch (Exception $e) {
            Log::error('Erro na validação do arquivo', [
                'file' => $source->getClientOriginalName(),
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    public function getPublicUrl(string $path): string
    {
        return Storage::disk('public')->url($path);
    }

    /**
     * Generate a unique filename for the uploaded file
     */
    protected function generateUniqueFilename(UploadedFile $file): string
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $filename = Str::uuid()->toString() . '.' . $extension;
        
        // Ensure the filename is unique in the storage
        while (Storage::disk('public')->exists('videos/' . $filename)) {
            $filename = Str::uuid()->toString() . '.' . $extension;
        }
        
        return $filename;
    }

    /**
     * Compress video file if needed
     */
    protected function compressVideo(string $sourcePath, array $options = []): ?string
    {
        try {
            if (!class_exists('FFMpeg\FFMpeg') || !$this->isFfmpegAvailable()) {
                Log::warning('FFmpeg não está disponível para compressão de vídeo');
                return null;
            }

            $ffmpeg = FFMpeg::create();
            $video = $ffmpeg->open($sourcePath);
            
            $outputPath = pathinfo($sourcePath, PATHINFO_DIRNAME) . '/' . 
                         pathinfo($sourcePath, PATHINFO_FILENAME) . '_compressed.' . 
                         pathinfo($sourcePath, PATHINFO_EXTENSION);

            $format = new \FFMpeg\Format\Video\X264();
            
            // Set video bitrate (default to 1000k if not specified)
            $bitrate = $options['bitrate'] ?? 1000;
            $format->setKiloBitrate($bitrate);

            // Save the compressed video
            $video->save($format, $outputPath);

            Log::info('Vídeo comprimido com sucesso', [
                'source' => $sourcePath,
                'output' => $outputPath,
                'bitrate' => $bitrate . 'k'
            ]);

            return $outputPath;

        } catch (Exception $e) {
            Log::error('Erro ao comprimir vídeo', [
                'source' => $sourcePath,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Check if FFmpeg is available in the system
     */
    protected function isFfmpegAvailable(): bool
    {
        $executableFinder = new ExecutableFinder();
        return $executableFinder->find('ffmpeg') !== null;
    }

    public function delete(string $path): bool
    {
        try {
            if (!Storage::disk('public')->exists($path)) {
                Log::warning('Arquivo não encontrado para exclusão', ['path' => $path]);
                return false;
            }
            
            // Delete thumbnail if exists
            $thumbnailPath = 'thumbnails/' . pathinfo($path, PATHINFO_FILENAME) . '.jpg';
            if (Storage::disk('public')->exists($thumbnailPath)) {
                Storage::disk('public')->delete($thumbnailPath);
            }
            
            return Storage::disk('public')->delete($path);
        } catch (Exception $e) {
            Log::error('Erro ao deletar arquivo local', [
                'path' => $path,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    public function exists(string $path): bool
    {
        return Storage::disk('public')->exists($path);
    }

    public function getType(): string
    {
        return 'local';
    }

    private function generateUniqueFilename(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        $timestamp = now()->format('Y-m-d_H-i-s');
        $uuid = Str::uuid();
        
        return "{$timestamp}_{$uuid}.{$extension}";
    }

    private function compressVideo(string $filePath, array $options = []): ?string
    {
        try {
            if (!class_exists('\\FFMpeg\\FFMpeg') || !isset($options['compress']) || !$options['compress']) {
                return null;
            }

            $ffmpeg = \FFMpeg\FFMpeg::create();
            $video = $ffmpeg->open($filePath);

            $compressedFilename = 'compressed_' . basename($filePath);
            $compressedPath = dirname($filePath) . '/' . $compressedFilename;

            // Configurar compressão
            $format = new \FFMpeg\Format\Video\X264();
            $format->setKiloBitrate(1000); // 1Mbps
            $format->setAudioCodec('aac');

            $video->save($format, $compressedPath);

            Log::info('Vídeo comprimido', [
                'original' => $filePath,
                'compressed' => $compressedPath
            ]);

            return $compressedPath;

        } catch (Exception $e) {
            Log::error('Erro ao comprimir vídeo', [
                'file' => $filePath,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
}