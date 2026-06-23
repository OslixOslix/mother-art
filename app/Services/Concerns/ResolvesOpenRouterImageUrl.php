<?php

namespace App\Services\Concerns;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait ResolvesOpenRouterImageUrl
{
    /**
     * @return array{0: string, 1: ?string} URL для API и путь временного файла на public-диске (если есть)
     */
    protected function resolveImageUrl(string $absolutePath): array
    {
        $fileSize = filesize($absolutePath);

        if ($fileSize !== false && $fileSize <= config('openrouter.import.max_base64_bytes')) {
            return [$this->toDataUrl($absolutePath), null];
        }

        $extension = strtolower(pathinfo($absolutePath, PATHINFO_EXTENSION));
        $tempPath = 'import-temp/'.Str::uuid().'.'.$extension;

        Storage::disk('public')->put($tempPath, file_get_contents($absolutePath));

        $url = rtrim(config('app.url'), '/').Storage::disk('public')->url($tempPath);

        return [$url, $tempPath];
    }

    protected function toDataUrl(string $absolutePath): string
    {
        $mime = match (strtolower(pathinfo($absolutePath, PATHINFO_EXTENSION))) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
            'gif' => 'image/gif',
            default => 'application/octet-stream',
        };

        return 'data:'.$mime.';base64,'.base64_encode(file_get_contents($absolutePath));
    }

    protected function decodeDataUrl(string $dataUrl): string
    {
        if (! str_starts_with($dataUrl, 'data:')) {
            throw new \RuntimeException('Некорректный формат изображения в ответе OpenRouter.');
        }

        $commaPosition = strpos($dataUrl, ',');

        if ($commaPosition === false) {
            throw new \RuntimeException('Некорректный data URL в ответе OpenRouter.');
        }

        $binary = base64_decode(substr($dataUrl, $commaPosition + 1), true);

        if ($binary === false) {
            throw new \RuntimeException('Не удалось декодировать изображение из ответа OpenRouter.');
        }

        return $binary;
    }
}
