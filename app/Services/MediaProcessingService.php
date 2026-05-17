<?php

namespace App\Services;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Format;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;
use Illuminate\Support\Facades\Storage;



class MediaProcessingService
{
public function generateVideoThumbnail($path)
{
    $fullPath = storage_path("app/public/{$path}");

    // safer filename handling
    $thumbnailPath = preg_replace('/\.(mp4|mov)$/i', '.jpg', $path);
    $outputPath = storage_path("app/public/{$thumbnailPath}");

    // 🔐 escape paths (CRITICAL)
    $input = escapeshellarg($fullPath);
    $output = escapeshellarg($outputPath);

    // 🎯 clean ffmpeg command
    $command = "ffmpeg -y -i {$input} -ss 00:00:01 -vframes 1 {$output} 2>&1";

    exec($command, $result, $status);

    // 🧠 basic failure guard
    if ($status !== 0) {
        logger('FFMPEG FAILED', [
            'command' => $command,
            'output' => $result
        ]);
        return null;
    }

    return $thumbnailPath;
}


public function optimizeImage($path)
{
    $fullPath = storage_path("app/public/{$path}");

    $manager = ImageManager::usingDriver(Driver::class);

    // ✅ correct method
    $image = $manager->decodePath($fullPath);

    $image = $image->scale(width: 1600);

    // ✅ encode + save
    $encoded = $image->encodeUsingFormat(Format::JPEG, quality: 80);

    $encoded->save($fullPath);

    return $path;
}

}
