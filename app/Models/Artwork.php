<?php

namespace App\Models;

use App\Enums\ArtworkImagePreset;
use Database\Factories\ArtworkFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

#[Fillable(['category_id', 'title', 'slug', 'price', 'width_cm', 'height_cm', 'description', 'image_path', 'is_published', 'published_at'])]
class Artwork extends Model
{
    /** @use HasFactory<ArtworkFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'is_published' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Artwork $artwork): void {
            if (blank($artwork->slug) && filled($artwork->title)) {
                $artwork->slug = static::uniqueSlug($artwork->title, $artwork->id);
            }

            if ($artwork->is_published && blank($artwork->published_at)) {
                $artwork->published_at = now();
            }

            if (! $artwork->is_published) {
                $artwork->published_at = null;
            }
        });

        static::deleting(function (Artwork $artwork): void {
            if (filled($artwork->image_path)) {
                Storage::disk('public')->delete($artwork->image_path);
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function orderRequests(): HasMany
    {
        return $this->hasMany(OrderRequest::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function imageUrl(?ArtworkImagePreset $preset = null): ?string
    {
        if (blank($this->image_path)) {
            return null;
        }

        if ($preset === null) {
            return Storage::disk('public')->url($this->image_path);
        }

        return route('images.show', [
            'preset' => $preset->value,
            'path' => $this->image_path,
        ]);
    }

    public function formattedPrice(): ?string
    {
        return $this->price ? number_format($this->price, 0, ',', ' ').' ₽' : null;
    }

    public function dimensions(): ?string
    {
        if (blank($this->width_cm) && blank($this->height_cm)) {
            return null;
        }

        $parts = array_filter(
            [$this->height_cm, $this->width_cm],
            fn ($value) => filled($value),
        );

        return implode(' × ', $parts).' см';
    }

    public function previousPublished(): ?self
    {
        return static::query()
            ->published()
            ->where(function (Builder $query): void {
                $query->where('published_at', '>', $this->published_at ?? now())
                    ->orWhere(function (Builder $query): void {
                        $query->where('published_at', $this->published_at)
                            ->where('id', '>', $this->id);
                    });
            })
            ->orderBy('published_at')
            ->orderBy('id')
            ->first();
    }

    public function nextPublished(): ?self
    {
        return static::query()
            ->published()
            ->where(function (Builder $query): void {
                $query->where('published_at', '<', $this->published_at ?? now())
                    ->orWhere(function (Builder $query): void {
                        $query->where('published_at', $this->published_at)
                            ->where('id', '<', $this->id);
                    });
            })
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->first();
    }

    public static function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: Str::random(8);
        $slug = $base;
        $counter = 2;

        while (static::query()
            ->when($ignoreId, fn (Builder $query) => $query->whereKeyNot($ignoreId))
            ->where('slug', $slug)
            ->exists()) {
            $slug = $base.'-'.$counter++;
        }

        return $slug;
    }
}
