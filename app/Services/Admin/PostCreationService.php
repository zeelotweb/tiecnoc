<?php

namespace App\Services\Creator;

use App\Models\Post;
use App\Models\Circle;
use App\Models\Media;
use App\Helpers\MediaPath;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Intervention\Image\Laravel\Facades\Image;
use App\Jobs\ProcessMediaJob;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PostCreationService
{
    /**
     * Handles media uploads for both standard Posts and ephemeral Circles.
     */
    public function handle($user, array $data, $circle = null): Model
    {
        return DB::transaction(function () use ($user, $data, $circle) {
            $mediaItems = json_decode($data['media'] ?? '[]', true) ?: [];

            // 1. CONDUCTOR: Ensure we have a Post as the "Anchor"
            if ($circle instanceof Circle) {
                $model = Post::create([
                    'user_id'            => $user->id,
                    'circle_id'          => $circle->id,
                    'description'        => $data['description'] ?? null,
                    // 🔥 FIX: Map 'ppv' to 'subscription' to satisfy DB CHECK constraints
                    'visibility'         => ($data['visibility'] === 'ppv') ? 'subscription' : ($data['visibility'] ?? 'public'),
                    'subscription_price' => $data['price'] ?? 0,
                    'is_published'       => true,
                    'published_at'       => now(),
                ]);
            } else {
                // Standard Feed Logic
                $model = isset($data['post_id']) 
                    ? Post::where('user_id', $user->id)->findOrFail($data['post_id']) 
                    : Post::create([
                        'user_id'            => $user->id,
                        'description'        => $data['description'] ?? null,
                        'visibility'         => $data['visibility'] ?? 'public',
                        'subscription_price' => $data['subscription_price'] ?? null,
                        'is_published'       => true,
                        'published_at'       => now(),
                    ]);
            }

            // 2. SENSOR: Set directory
            $baseDir = "media/users/{$user->id}/posts/{$model->id}";
            $currentMaxOrder = $model->media()->max('sort_order');
            $startOrder = is_null($currentMaxOrder) ? 0 : $currentMaxOrder + 1;

            foreach ($mediaItems as $index => $item) {
                if (empty($item['file'])) continue;

                $tempPath = "temp/{$item['file']}";
                if (!Storage::disk('public')->exists($tempPath)) continue;

                $absPath   = Storage::disk('public')->path($tempPath);
                $mime      = File::mimeType($absPath);
                $extension = strtolower(pathinfo($item['file'], PATHINFO_EXTENSION));
                
                $filename  = MediaPath::filename($user->id, $extension);
                $finalPath = "{$baseDir}/files/{$filename}";
                $thumbPath = "{$baseDir}/thumbs/thumb_{$filename}";

                $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'heic']);
                $thumbnailUrl = null;

                if ($isImage) {
                    try {
                        $img = Image::read($absPath);

                        // Generate Thumbnail
                        $thumb = clone $img;
                        $thumb->scale(width: 600); 
                        Storage::disk('public')->put($thumbPath, $thumb->toJpeg(70));
                        $thumbnailUrl = Storage::disk('public')->url($thumbPath);

                        // Optimize Original
                        $img->scale(width: 2000);
                        Storage::disk('public')->put($finalPath, $img->encodeByExtension($extension, 85));
                        Storage::disk('public')->delete($tempPath);
                    } catch (\Exception $e) {
                        Storage::disk('public')->move($tempPath, $finalPath);
                    }
                } else {
                    Storage::disk('public')->move($tempPath, $finalPath);
                }

                // 3. Attach Media to the Post (The Anchor)
                $media = $model->media()->create([
                    'user_id'       => $user->id,
                    'disk'          => 'public',
                    'filename'      => $filename,
                    'original_name' => $item['file'],
                    'path'          => $finalPath,
                    'thumbnail_url' => $thumbnailUrl,
                    'mime_type'     => $mime,
                    'size'          => Storage::disk('public')->size($finalPath),
                    'sort_order'    => $startOrder + $index,
                    'is_processed'  => $isImage,
                    'is_ppv'        => $data['is_ppv'] ?? false,
                    'price'         => $data['price'] ?? 0,
                ]);

                if (!$isImage) {
                    ProcessMediaJob::dispatch($media);
                }
            }

            return $model;
        });
    }
}
