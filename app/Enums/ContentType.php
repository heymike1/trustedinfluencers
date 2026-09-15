<?php

namespace App\Enums;

enum ContentType: string
{
    case Video = 'video';   // YouTube long-form
    case Short = 'short';   // YouTube Shorts
    case Reel = 'reel';     // Instagram Reels
    case Post = 'post';     // Instagram feed posts, X posts

    public function label(): string
    {
        return match ($this) {
            self::Video => 'videos',
            self::Short => 'Shorts',
            self::Reel => 'Reels',
            self::Post => 'posts',
        };
    }

    public function singular(): string
    {
        return match ($this) {
            self::Video => 'video',
            self::Short => 'Short',
            self::Reel => 'Reel',
            self::Post => 'post',
        };
    }
}
