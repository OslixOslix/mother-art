<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class OpenRouterImageService
{
    /**
     * Обрабатывает изображение через OpenRouter и возвращает бинарные данные PNG.
     */
    public function processForProductCard(string $absolutePath): string
    {
        $apiKey = config('openrouter.api_key');

        if (blank($apiKey)) {
            throw new RuntimeException('Не задан OPENROUTER_API_KEY.');
        }

        if (! is_file($absolutePath)) {
            throw new RuntimeException("Файл не найден: {$absolutePath}");
        }

        [$imageUrl, $tempPath] = $this->resolveImageUrl($absolutePath);

        try {
            $payload = [
                'model' => config('openrouter.model'),
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => [
                            [
                                'type' => 'image_url',
                                'image_url' => ['url' => $imageUrl],
                            ],
                            [
                                'type' => 'text',
                                'text' => config('openrouter.import.prompt'),
                            ],
                        ],
                    ],
                ],
                'modalities' => config('openrouter.import.modalities'),
                'image_config' => config('openrouter.import.image_config'),
            ];

            $response = Http::withToken($apiKey)
                ->withHeaders([
                    'HTTP-Referer' => config('openrouter.http_referer'),
                    'X-Title' => config('openrouter.app_title'),
                ])
                ->timeout(config('openrouter.timeout'))
                ->post(config('openrouter.base_url').'/chat/completions', $payload);

            if (! $response->successful()) {
                throw new RuntimeException(
                    'OpenRouter вернул ошибку '.$response->status().': '.$response->body()
                );
            }

            $data = $response->json();
            $imageDataUrl = data_get($data, 'choices.0.message.images.0.image_url.url');

            if (! is_string($imageDataUrl) || $imageDataUrl === '') {
                throw new RuntimeException(
                    'OpenRouter не вернул изображение: '.json_encode($data, JSON_UNESCAPED_UNICODE)
                );
            }

            Log::info('OpenRouter: изображение обработано', [
                'source' => basename($absolutePath),
                'model' => config('openrouter.model'),
            ]);

            return $this->decodeDataUrl($imageDataUrl);
        } finally {
            if ($tempPath !== null) {
                Storage::disk('public')->delete($tempPath);
            }
        }
    }

    /**
     * @return array{0: string, 1: ?string} URL для API и путь временного файла на public-диске (если есть)
     */
    private function resolveImageUrl(string $absolutePath): array
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

    private function toDataUrl(string $absolutePath): string
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

    private function decodeDataUrl(string $dataUrl): string
    {
        if (! str_starts_with($dataUrl, 'data:')) {
            throw new RuntimeException('Некорректный формат изображения в ответе OpenRouter.');
        }

        $commaPosition = strpos($dataUrl, ',');

        if ($commaPosition === false) {
            throw new RuntimeException('Некорректный data URL в ответе OpenRouter.');
        }

        $binary = base64_decode(substr($dataUrl, $commaPosition + 1), true);

        if ($binary === false) {
            throw new RuntimeException('Не удалось декодировать изображение из ответа OpenRouter.');
        }

        return $binary;
    }
}
