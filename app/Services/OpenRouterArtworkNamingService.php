<?php

namespace App\Services;

use App\Services\Concerns\ResolvesOpenRouterImageUrl;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class OpenRouterArtworkNamingService
{
    use ResolvesOpenRouterImageUrl;

    /**
     * @return array{title: string, slug: string, quatrain: string}
     */
    public function generateMetadata(string $absolutePath): array
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
                'model' => config('openrouter.naming.model'),
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
                                'text' => config('openrouter.naming.prompt'),
                            ],
                        ],
                    ],
                ],
                'response_format' => [
                    'type' => 'json_schema',
                    'json_schema' => config('openrouter.naming.json_schema'),
                ],
                'plugins' => [
                    ['id' => 'response-healing'],
                ],
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

            $content = data_get($response->json(), 'choices.0.message.content');

            if (! is_string($content) || $content === '') {
                throw new RuntimeException(
                    'OpenRouter не вернул текстовый ответ: '.json_encode($response->json(), JSON_UNESCAPED_UNICODE)
                );
            }

            $metadata = json_decode($content, true);

            if (! is_array($metadata)) {
                throw new RuntimeException('OpenRouter вернул некорректный JSON: '.$content);
            }

            foreach (['title', 'slug', 'quatrain'] as $field) {
                if (! is_string($metadata[$field] ?? null) || $metadata[$field] === '') {
                    throw new RuntimeException("OpenRouter не вернул поле «{$field}».");
                }
            }

            Log::info('OpenRouter: метаданные картины сгенерированы', [
                'source' => basename($absolutePath),
                'model' => config('openrouter.naming.model'),
            ]);

            return [
                'title' => $metadata['title'],
                'slug' => $metadata['slug'],
                'quatrain' => $metadata['quatrain'],
            ];
        } finally {
            if ($tempPath !== null) {
                Storage::disk('public')->delete($tempPath);
            }
        }
    }
}
