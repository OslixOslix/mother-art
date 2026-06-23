<?php

namespace App\Enums;

enum ArtworkImagePreset: string
{
    case Admin = 'admin';
    case Thumb = 'thumb';
    case Card = 'card';
    case CardPortrait = 'card-portrait';
    case CardLandscape = 'card-landscape';
    case CardSquare = 'card-square';
    case Hero = 'hero';
    case Detail = 'detail';

    public static function forCardVariant(string $variant): self
    {
        return match ($variant) {
            'featured-large' => self::CardLandscape,
            'featured-side' => self::CardPortrait,
            'featured-small' => self::CardSquare,
            default => self::Card,
        };
    }
}
