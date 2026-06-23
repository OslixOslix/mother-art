<?php

namespace App\Services;

use App\Enums\ArtworkImagePreset;
use Illuminate\Support\Facades\Storage;
use League\Glide\Server;
use League\Glide\ServerFactory;

class ArtworkImageServer
{
    private ?Server $server = null;

    public function server(): Server
    {
        if ($this->server !== null) {
            return $this->server;
        }

        $publicRoot = Storage::disk('public')->path('');

        $this->server = ServerFactory::create([
            'source' => $publicRoot,
            'cache' => $publicRoot.'glide-cache',
            'driver' => config('artwork-images.driver', 'gd'),
            'presets' => config('artwork-images.presets', []),
        ]);

        return $this->server;
    }

    /**
     * @return array<string, mixed>
     */
    public function paramsForPreset(ArtworkImagePreset $preset): array
    {
        $presets = config('artwork-images.presets', []);

        if (! array_key_exists($preset->value, $presets)) {
            throw new \InvalidArgumentException("Неизвестный пресет изображения: {$preset->value}");
        }

        return ['p' => $preset->value];
    }

    public function isAllowedSourcePath(string $path): bool
    {
        $prefix = (string) config('artwork-images.source_path_prefix', 'artworks/');

        if ($path === '' || str_contains($path, '..')) {
            return false;
        }

        return str_starts_with($path, $prefix);
    }

    public function presetExists(string $preset): bool
    {
        return array_key_exists($preset, config('artwork-images.presets', []));
    }

    public function makeImage(string $path, ArtworkImagePreset $preset): string
    {
        return $this->server()->makeImage($path, $this->paramsForPreset($preset));
    }
}
