<?php

namespace App\Helpers;

use Illuminate\Support\Str;

class MediaPath
{
    /**
     * Generate the base directory for a post's media.
     * Example: users/1/posts/42
     */
    public static function baseDir(int $userId, int $postId): string
    {
        return "gallery/{$userId}/merch/{$postId}";
    }

    /**
     * Generate a unique filename while preserving the extension.
     * Example: 550e8400-e29b-41d4-a716-446655440000.jpg
     */
    public static function filename(int $userId, string $extension): string
    {
        return (string) Str::uuid() . '.' . $extension;
    }
}
