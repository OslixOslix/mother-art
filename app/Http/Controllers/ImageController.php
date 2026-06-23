<?php

namespace App\Http\Controllers;

use App\Services\ArtworkImageServer;
use Illuminate\Http\Response;
use League\Glide\Filesystem\FileNotFoundException;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class ImageController extends Controller
{
    public function __construct(private ArtworkImageServer $images) {}

    public function show(string $preset, string $path): Response
    {
        if (! $this->images->presetExists($preset) || ! $this->images->isAllowedSourcePath($path)) {
            abort(HttpResponse::HTTP_NOT_FOUND);
        }

        $server = $this->images->server();

        if (! $server->sourceFileExists($path)) {
            abort(HttpResponse::HTTP_NOT_FOUND);
        }

        try {
            $cachedPath = $server->makeImage($path, ['p' => $preset]);
        } catch (FileNotFoundException) {
            abort(HttpResponse::HTTP_NOT_FOUND);
        }

        $cache = $server->getCache();

        return response(
            $cache->read($cachedPath),
            HttpResponse::HTTP_OK,
            [
                'Content-Type' => $cache->mimeType($cachedPath),
                'Content-Length' => (string) $cache->fileSize($cachedPath),
                'Cache-Control' => 'public, max-age=31536000',
                'Expires' => now()->addYear()->toRfc7231String(),
            ],
        );
    }
}
