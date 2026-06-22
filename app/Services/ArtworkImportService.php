<?php

namespace App\Services;

use App\Models\Artwork;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ArtworkImportService
{
    /**
     * @return array{created: int, ignored: int}
     */
    public function importFrom(string $path): array
    {
        File::ensureDirectoryExists($path);

        $created = 0;
        $ignored = 0;

        foreach (File::files($path) as $file) {
            if (! in_array(strtolower($file->getExtension()), ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)) {
                $ignored++;

                continue;
            }

            $title = Str::of($file->getFilenameWithoutExtension())
                ->replace(['_', '-'], ' ')
                ->squish()
                ->title()
                ->toString();

            $extension = strtolower($file->getExtension());
            $destination = 'artworks/'.Str::uuid().'.'.$extension;

            Storage::disk('public')->put($destination, File::get($file->getPathname()));
            File::delete($file->getPathname());

            Artwork::create([
                'title' => $title ?: 'Новая работа',
                'slug' => Artwork::uniqueSlug($title ?: 'Новая работа'),
                'image_path' => $destination,
                'is_published' => false,
            ]);

            $created++;
        }

        return compact('created', 'ignored');
    }
}
