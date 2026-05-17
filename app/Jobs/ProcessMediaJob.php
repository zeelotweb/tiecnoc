<?php

namespace App\Jobs;

use App\Models\Media;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;
use Illuminate\Support\Facades\Storage;

class ProcessMediaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Media $media;

    public function __construct(Media $media)
    {
        $this->media = $media;
    }

    public function handle()
    {
        $media = $this->media->fresh();

        // 🛡️ Guard against deleted records or missing source files
        if (!$media || !Storage::disk($media->disk)->exists($media->path)) {
            return;
        }

        // ⚡ ALIGNMENT 1: Checking both standard mime paths and generic fallback tags
        $imageTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/heic', 'image/heif', 'image'];
        
        if (in_array($media->type, $imageTypes) || str_starts_with($media->type, 'image')) {
            $media->update(['status' => 'ready']); 
            return;
        }

        // Only proceed with FFmpeg for Video files
        if (!str_starts_with($media->type, 'video')) {
            $media->update(['status' => 'ready']);
            return;
        }

        try {
            $media->update(['status' => 'processing']);

            // Derived paths from your Products anchor directory mapping
            $baseDir = dirname(dirname($media->path)); 
            $thumbDir = $baseDir . '/thumbs';
            
            if (!Storage::disk($media->disk)->exists($thumbDir)) {
                Storage::disk($media->disk)->makeDirectory($thumbDir);
            }

            // Using pathinfo to grab the clean UUID filename from your file path
            $filename = pathinfo($media->path, PATHINFO_FILENAME);
            $thumbPath = $thumbDir . '/thumb_' . $filename . '.jpg';

            // Generate thumbnail at 1-second mark using ProtoneMedia bridge
            FFMpeg::fromDisk($media->disk)
                ->open($media->path)
                ->getFrameFromSeconds(1)
                ->export()
                ->toDisk($media->disk)
                ->save($thumbPath);

            // ⚡ ALIGNMENT 2: Mapping to your provided schema columns
            $media->update([
                'thumbnail' => $thumbPath, // 👈 Maps to 'thumbnail' instead of 'thumbnail_url'
                'status'    => 'ready',
            ]);
            
        } catch (\Throwable $e) {
            \Log::error("Media Processing Failed for Media ID {$media->id}: " . $e->getMessage());
            
            $media->update([
                'status' => 'failed',
            ]);
        }
    }
}
