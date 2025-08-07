<?php

namespace App\Contracts;

use Illuminate\Http\UploadedFile;

interface StorageHandlerInterface
{
    /**
     * Armazenar arquivo e retornar informações
     */
    public function store(UploadedFile $file, array $options = []): array;

    /**
     * Extrair metadados do arquivo/URL
     */
    public function extractMetadata(string $source): array;

    /**
     * Gerar thumbnail
     */
    public function generateThumbnail(string $source, array $options = []): ?string;

    /**
     * Validar arquivo/fonte
     */
    public function validate($source): bool;

    /**
     * Obter URL pública do arquivo
     */
    public function getPublicUrl(string $path): string;

    /**
     * Deletar arquivo
     */
    public function delete(string $path): bool;

    /**
     * Verificar se arquivo existe
     */
    public function exists(string $path): bool;

    /**
     * Obter tipo de handler
     */
    public function getType(): string;
}