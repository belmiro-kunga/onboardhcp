<?php

namespace App\Services\Storage;

use App\Contracts\StorageHandlerInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;
use Aws\S3\S3Client;
use Aws\Exception\AwsException;

class CloudflareR2Handler implements StorageHandlerInterface
{
    private $client;
    private $bucket;
    private $region;
    private $endpoint;
    private $publicUrl;

    public function __construct()
    {
        $this->bucket = config('services.cloudflare_r2.bucket');
        $this->region = config('services.cloudflare_r2.region', 'auto');
        $this->endpoint = config('services.cloudflare_r2.endpoint');
        $this->publicUrl = config('services.cloudflare_r2.public_url');

        $this->client = new S3Client([
            'version' => 'latest',
            'region' => $this->region,
            'endpoint' => $this->endpoint,
            'credentials' => [
                'key' => config('services.cloudflare_r2.access_key_id'),
                'secret' => config('services.cloudflare_r2.secret_access_key'),
            ],
            'use_path_style_endpoint' => true,
        ]);
    }

    public function store(UploadedFile $file, array $options = []): array
    {
        try {
            // Validar arquivo
            if (!$this->validate($file)) {
                throw new Exception('Arquivo inválido ou não suportado.');
            }

            // Gerar chave única
            $key = $this->generateUniqueKey($file, $options);
            
            // Configurações de upload
            $uploadOptions = [
                'Bucket' => $this->bucket,
                'Key' => $key,
                'Body' => fopen($file->getPathname(), 'r'),
                'ContentType' => $file->getMimeType(),
                'ACL' => 'public-read',
                'Metadata' => [
                    'original-name' => $file->getClientOriginalName(),
                    'uploaded-at' => now()->toISOString(),
                    'size' => (string) $file->getSize()
                ]
            ];

            // Adicionar configurações personalizadas
            if (isset($options['cache_control'])) {
                $uploadOptions['CacheControl'] = $options['cache_control'];
            }

            // Upload para R2
            $result = $this->client->putObject($uploadOptions);

            // Extrair metadados
            $metadata = $this->extractMetadata($file->getPathname());
            $metadata['r2_etag'] = $result['ETag'] ?? null;
            $metadata['r2_version_id'] = $result['VersionId'] ?? null;

            // Gerar thumbnail se for vídeo
            $thumbnailUrl = null;
            if (str_starts_with($file->getMimeType(), 'video/')) {
                $thumbnailUrl = $this->generateThumbnail($key, $options);
            }

            $publicUrl = $this->getPublicUrl($key);

            Log::info('Arquivo enviado para Cloudflare R2', [
                'key' => $key,
                'bucket' => $this->bucket,
                'size' => $file->getSize(),
                'public_url' => $publicUrl
            ]);

            return [
                'success' => true,
                'path' => $key,
                'url' => $publicUrl,
                'thumbnail' => $thumbnailUrl,
                'metadata' => $metadata,
                'size' => $file->getSize(),
                'original_name' => $file->getClientOriginalName(),
                'etag' => $result['ETag'] ?? null
            ];

        } catch (AwsException $e) {
            Log::error('Erro AWS ao enviar para R2', [
                'message' => $e->getMessage(),
                'code' => $e->getAwsErrorCode(),
                'file' => $file->getClientOriginalName()
            ]);
            throw new Exception('Erro ao enviar arquivo para Cloudflare R2: ' . $e->getMessage());
        } catch (Exception $e) {
            Log::error('Erro ao enviar arquivo para R2', [
                'message' => $e->getMessage(),
                'file' => $file->getClientOriginalName()
            ]);
            throw $e;
        }
    }

    public function extractMetadata(string $filePath): array
    {
        try {
            $metadata = [
                'file_size' => filesize($filePath),
                'mime_type' => mime_content_type($filePath),
                'uploaded_at' => now()->toISOString()
            ];

            // Usar FFMpeg para vídeos se disponível
            // Substituir str_starts_with por substr
            if (class_exists('FFMpeg\\FFMpeg') && substr($metadata['mime_type'], 0, 6) === 'video/') {
                try {
                    $ffmpeg = \FFMpeg\FFMpeg::create();
                    $video = $ffmpeg->open($filePath);
                    $format = $video->getFormat();

                    $metadata['duration'] = $format->get('duration');
                    $metadata['bitrate'] = $format->get('bit_rate');
                    $metadata['width'] = $format->get('width');
                    $metadata['height'] = $format->get('height');
                    $metadata['codec'] = $format->get('codec_name');
                } catch (Exception $e) {
                    Log::warning('Erro ao extrair metadados de vídeo para R2', [
                        'error' => $e->getMessage()
                    ]);
                }
            }

            return $metadata;

        } catch (Exception $e) {
            Log::error('Erro ao extrair metadados para R2', [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    public function generateThumbnail(string $key, array $options = []): ?string
    {
        try {
            // Para R2, podemos usar Cloudflare Images ou gerar localmente e enviar
            $thumbnailKey = 'thumbnails/' . pathinfo($key, PATHINFO_FILENAME) . '.jpg';
            
            // Se tiver FFMpeg disponível, gerar thumbnail local e enviar
            if (class_exists('FFMpeg\\FFMpeg')) {
                $tempFile = tempnam(sys_get_temp_dir(), 'thumbnail_');
                
                // Baixar arquivo temporariamente para gerar thumbnail
                $videoContent = $this->client->getObject([
                    'Bucket' => $this->bucket,
                    'Key' => $key
                ])['Body'];
                
                file_put_contents($tempFile, $videoContent);
                
                $ffmpeg = \FFMpeg\FFMpeg::create();
                $video = $ffmpeg->open($tempFile);
                
                $thumbnailTemp = tempnam(sys_get_temp_dir(), 'thumb_') . '.jpg';
                $frame = $video->frame(\FFMpeg\Coordinate\TimeCode::fromSeconds(1));
                $frame->save($thumbnailTemp);
                
                // Enviar thumbnail para R2
                $this->client->putObject([
                    'Bucket' => $this->bucket,
                    'Key' => $thumbnailKey,
                    'Body' => fopen($thumbnailTemp, 'r'),
                    'ContentType' => 'image/jpeg',
                    'ACL' => 'public-read'
                ]);
                
                // Limpar arquivos temporários
                unlink($tempFile);
                unlink($thumbnailTemp);
                
                return $this->getPublicUrl($thumbnailKey);
            }
            
            return null;
            
        } catch (Exception $e) {
            Log::error('Erro ao gerar thumbnail no R2', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    public function validate($source): bool
    {
        if (!$source instanceof UploadedFile) {
            return false;
        }

        // Verificar tamanho (R2 suporta até 5TB, mas vamos limitar)
        $maxSize = 2 * 1024 * 1024 * 1024; // 2GB
        if ($source->getSize() > $maxSize) {
            return false;
        }

        // Verificar tipo de arquivo
        $allowedMimes = [
            'video/mp4', 'video/avi', 'video/quicktime', 'video/x-msvideo',
            'video/x-flv', 'video/webm', 'video/x-matroska'
        ];
        
        return in_array($source->getMimeType(), $allowedMimes);
    }

    public function getPublicUrl(string $key): string
    {
        return rtrim($this->publicUrl, '/') . '/' . ltrim($key, '/');
    }

    public function delete(string $key): bool
    {
        try {
            $this->client->deleteObject([
                'Bucket' => $this->bucket,
                'Key' => $key
            ]);
            
            Log::info('Arquivo deletado do R2', ['key' => $key]);
            return true;
            
        } catch (AwsException $e) {
            Log::error('Erro ao deletar arquivo do R2', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    public function exists(string $key): bool
    {
        try {
            $this->client->headObject([
                'Bucket' => $this->bucket,
                'Key' => $key
            ]);
            return true;
        } catch (AwsException $e) {
            return false;
        }
    }

    public function getType(): string
    {
        return 'r2';
    }

    private function generateUniqueKey(UploadedFile $file, array $options = []): string
    {
        $directory = $options['directory'] ?? 'videos';
        $timestamp = now()->format('Y/m/d');
        $uuid = Str::uuid();
        $extension = $file->getClientOriginalExtension();
        
        return "{$directory}/{$timestamp}/{$uuid}.{$extension}";
    }
}