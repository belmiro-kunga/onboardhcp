<?php

namespace App\Services;

use App\Contracts\StorageHandlerInterface;
use App\Services\Storage\LocalStorageHandler;
use App\Services\Storage\YouTubeHandler;
use App\Services\Storage\CloudflareR2Handler;
use App\Models\Video;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Exception;

class StorageService
{
    private array $handlers = [];

    public function __construct(
        LocalStorageHandler $localHandler,
        YouTubeHandler $youtubeHandler,
        CloudflareR2Handler $r2Handler
    ) {
        $this->handlers = [
            'local' => $localHandler,
            'youtube' => $youtubeHandler,
            'r2' => $r2Handler
        ];
    }

    /**
     * Armazenar vídeo usando o handler apropriado
     */
    public function storeVideo($source, string $type, array $options = []): array
    {
        try {
            $handler = $this->getHandler($type);
            
            if ($type === 'youtube') {
                // Para YouTube, source é uma URL
                if (!is_string($source)) {
                    throw new Exception('Para YouTube, forneça uma URL válida.');
                }
                
                $metadata = $handler->extractMetadata($source);
                $thumbnail = $handler->generateThumbnail($source, $options);
                
                return [
                    'success' => true,
                    'path' => $source,
                    'url' => $source,
                    'thumbnail' => $thumbnail,
                    'metadata' => $metadata,
                    'type' => 'youtube'
                ];
            } else {
                // Para local e R2, source é um UploadedFile
                if (!$source instanceof UploadedFile) {
                    throw new Exception('Para armazenamento local/R2, forneça um arquivo válido.');
                }
                
                return $handler->store($source, $options);
            }
            
        } catch (Exception $e) {
            Log::error('Erro no StorageService', [
                'type' => $type,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Criar vídeo no banco de dados
     */
    public function createVideo(array $data): Video
    {
        try {
            // Validar dados obrigatórios
            $requiredFields = ['course_id', 'title', 'source_type'];
            foreach ($requiredFields as $field) {
                if (!isset($data[$field])) {
                    throw new Exception("Campo obrigatório ausente: {$field}");
                }
            }

            // Processar armazenamento
            $source = $data['source'] ?? null;
            $storageResult = $this->storeVideo($source, $data['source_type'], $data['options'] ?? []);

            // Criar registro no banco
            $videoData = [
                'course_id' => $data['course_id'],
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'source_type' => $data['source_type'],
                'source_url' => $storageResult['url'],
                'duration' => $storageResult['metadata']['duration'] ?? null,
                'thumbnail' => $storageResult['thumbnail'],
                'metadata' => $storageResult['metadata'],
                'order_index' => $data['order_index'] ?? 0,
                'is_active' => $data['is_active'] ?? true,
                'processed_at' => now()
            ];

            $video = Video::create($videoData);

            Log::info('Vídeo criado com sucesso', [
                'video_id' => $video->id,
                'source_type' => $video->source_type,
                'title' => $video->title
            ]);

            return $video;

        } catch (Exception $e) {
            Log::error('Erro ao criar vídeo', [
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Atualizar vídeo existente
     */
    public function updateVideo(Video $video, array $data): Video
    {
        try {
            // Se mudou a fonte, reprocessar
            if (isset($data['source']) && $data['source'] !== $video->source_url) {
                $storageResult = $this->storeVideo(
                    $data['source'], 
                    $data['source_type'] ?? $video->source_type,
                    $data['options'] ?? []
                );
                
                // Deletar arquivo antigo se for local ou R2
                if (in_array($video->source_type, ['local', 'r2'])) {
                    $this->deleteVideo($video);
                }
                
                $data['source_url'] = $storageResult['url'];
                $data['thumbnail'] = $storageResult['thumbnail'];
                $data['metadata'] = $storageResult['metadata'];
                $data['duration'] = $storageResult['metadata']['duration'] ?? null;
                $data['processed_at'] = now();
            }

            $video->update($data);

            Log::info('Vídeo atualizado', [
                'video_id' => $video->id,
                'updated_fields' => array_keys($data)
            ]);

            return $video->fresh();

        } catch (Exception $e) {
            Log::error('Erro ao atualizar vídeo', [
                'video_id' => $video->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Deletar vídeo e arquivos associados
     */
    public function deleteVideo(Video $video): bool
    {
        try {
            $handler = $this->getHandler($video->source_type);
            
            // Deletar arquivo físico (se aplicável)
            if ($video->source_type !== 'youtube') {
                $path = $this->extractPathFromUrl($video->source_url, $video->source_type);
                if ($path) {
                    $handler->delete($path);
                }
                
                // Deletar thumbnail se existir
                if ($video->thumbnail) {
                    $thumbnailPath = $this->extractPathFromUrl($video->thumbnail, $video->source_type);
                    if ($thumbnailPath) {
                        $handler->delete($thumbnailPath);
                    }
                }
            }

            // Deletar registro do banco
            $deleted = $video->delete();

            Log::info('Vídeo deletado', [
                'video_id' => $video->id,
                'source_type' => $video->source_type
            ]);

            return $deleted;

        } catch (Exception $e) {
            Log::error('Erro ao deletar vídeo', [
                'video_id' => $video->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Obter handler específico
     */
    public function getHandler(string $type): StorageHandlerInterface
    {
        if (!isset($this->handlers[$type])) {
            throw new Exception("Handler não encontrado para tipo: {$type}");
        }

        return $this->handlers[$type];
    }

    /**
     * Listar tipos de storage disponíveis
     */
    public function getAvailableTypes(): array
    {
        return array_keys($this->handlers);
    }

    /**
     * Validar fonte para tipo específico
     */
    public function validateSource($source, string $type): bool
    {
        try {
            $handler = $this->getHandler($type);
            return $handler->validate($source);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Extrair caminho do arquivo da URL
     */
    private function extractPathFromUrl(string $url, string $type): ?string
    {
        switch ($type) {
            case 'local':
                // Extrair caminho relativo do storage público
                if (str_contains($url, '/storage/')) {
                    return str_replace('/storage/', '', parse_url($url, PHP_URL_PATH));
                }
                break;
                
            case 'r2':
                // Extrair chave do R2
                $publicUrl = config('services.cloudflare_r2.public_url');
                if (str_starts_with($url, $publicUrl)) {
                    return str_replace($publicUrl . '/', '', $url);
                }
                break;
                
            case 'youtube':
                return null; // YouTube não permite deleção via API
        }
        
        return null;
    }
}