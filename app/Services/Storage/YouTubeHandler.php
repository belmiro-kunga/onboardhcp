<?php

namespace App\Services\Storage;

use App\Contracts\StorageHandlerInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Exception;

class YouTubeHandler implements StorageHandlerInterface
{
    private string $apiKey;
    private string $baseUrl = 'https://www.googleapis.com/youtube/v3';

    public function __construct()
    {
        $this->apiKey = config('services.youtube.api_key');
    }

    public function store(UploadedFile $file, array $options = []): array
    {
        throw new Exception('Upload direto para YouTube não implementado. Use extractMetadata com URL do YouTube.');
    }

    public function extractMetadata(string $youtubeUrl): array
    {
        try {
            $videoId = $this->extractVideoId($youtubeUrl);
            
            if (!$videoId) {
                throw new Exception('URL do YouTube inválida.');
            }

            // Cache por 1 hora
            $cacheKey = "youtube_metadata_{$videoId}";
            
            return Cache::remember($cacheKey, 3600, function () use ($videoId, $youtubeUrl) {
                return $this->fetchVideoMetadata($videoId, $youtubeUrl);
            });

        } catch (Exception $e) {
            Log::error('Erro ao extrair metadados do YouTube', [
                'url' => $youtubeUrl,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function generateThumbnail(string $youtubeUrl, array $options = []): ?string
    {
        try {
            $videoId = $this->extractVideoId($youtubeUrl);
            
            if (!$videoId) {
                return null;
            }

            // YouTube fornece thumbnails automáticos
            $quality = $options['quality'] ?? 'mqdefault'; // mqdefault, hqdefault, sddefault, maxresdefault
            
            return "https://img.youtube.com/vi/{$videoId}/{$quality}.jpg";

        } catch (Exception $e) {
            Log::error('Erro ao gerar thumbnail do YouTube', [
                'url' => $youtubeUrl,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    public function validate($source): bool
    {
        if (!is_string($source)) {
            return false;
        }

        return $this->extractVideoId($source) !== null;
    }

    public function getPublicUrl(string $path): string
    {
        return $path; // YouTube URLs são já públicas
    }

    public function delete(string $path): bool
    {
        // Não é possível deletar vídeos do YouTube via API
        Log::warning('Tentativa de deletar vídeo do YouTube', ['url' => $path]);
        return false;
    }

    public function exists(string $path): bool
    {
        try {
            $videoId = $this->extractVideoId($path);
            
            if (!$videoId) {
                return false;
            }

            $response = Http::get($this->baseUrl . '/videos', [
                'part' => 'id',
                'id' => $videoId,
                'key' => $this->apiKey
            ]);

            return $response->successful() && count($response->json('items', [])) > 0;

        } catch (Exception $e) {
            Log::error('Erro ao verificar existência do vídeo YouTube', [
                'url' => $path,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    public function getType(): string
    {
        return 'youtube';
    }

    private function extractVideoId(string $url): ?string
    {
        $patterns = [
            '/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]{11})/',
            '/youtube\.com\/embed\/([a-zA-Z0-9_-]{11})/',
            '/youtube\.com\/v\/([a-zA-Z0-9_-]{11})/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    private function fetchVideoMetadata(string $videoId, string $originalUrl): array
    {
        try {
            $response = Http::get($this->baseUrl . '/videos', [
                'part' => 'snippet,contentDetails,statistics,status',
                'id' => $videoId,
                'key' => $this->apiKey
            ]);

            if (!$response->successful()) {
                throw new Exception('Falha na API do YouTube: ' . $response->status());
            }

            $data = $response->json();
            $items = $data['items'] ?? [];

            if (empty($items)) {
                throw new Exception('Vídeo não encontrado no YouTube.');
            }

            $video = $items[0];
            $snippet = $video['snippet'] ?? [];
            $contentDetails = $video['contentDetails'] ?? [];
            $statistics = $video['statistics'] ?? [];
            $status = $video['status'] ?? [];

            // Converter duração ISO 8601 para segundos
            $duration = $this->parseDuration($contentDetails['duration'] ?? 'PT0S');

            $metadata = [
                'video_id' => $videoId,
                'title' => $snippet['title'] ?? '',
                'description' => $snippet['description'] ?? '',
                'duration' => $duration,
                'published_at' => $snippet['publishedAt'] ?? null,
                'channel_id' => $snippet['channelId'] ?? '',
                'channel_title' => $snippet['channelTitle'] ?? '',
                'view_count' => (int) ($statistics['viewCount'] ?? 0),
                'like_count' => (int) ($statistics['likeCount'] ?? 0),
                'comment_count' => (int) ($statistics['commentCount'] ?? 0),
                'privacy_status' => $status['privacyStatus'] ?? 'unknown',
                'embeddable' => $status['embeddable'] ?? false,
                'tags' => $snippet['tags'] ?? [],
                'category_id' => $snippet['categoryId'] ?? null,
                'default_language' => $snippet['defaultLanguage'] ?? null,
                'thumbnails' => $snippet['thumbnails'] ?? [],
                'original_url' => $originalUrl
            ];

            Log::info('Metadados do YouTube extraídos', [
                'video_id' => $videoId,
                'title' => $metadata['title'],
                'duration' => $metadata['duration']
            ]);

            return $metadata;

        } catch (Exception $e) {
            Log::error('Erro ao buscar metadados na API do YouTube', [
                'video_id' => $videoId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    private function parseDuration(string $duration): int
    {
        // Converter ISO 8601 duration (PT4M13S) para segundos
        preg_match('/PT(?:(\d+)H)?(?:(\d+)M)?(?:(\d+)S)?/', $duration, $matches);
        
        $hours = (int) ($matches[1] ?? 0);
        $minutes = (int) ($matches[2] ?? 0);
        $seconds = (int) ($matches[3] ?? 0);
        
        return ($hours * 3600) + ($minutes * 60) + $seconds;
    }
}