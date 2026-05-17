<?php

namespace App\Services\Admin;

use App\Models\Product;
use App\Helpers\MediaPath;
use App\Jobs\ProcessMediaJob;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class ProductMediaService
{
    /**
     * Handles media uploads for Product entities strictly as an anchor check.
     */
    public function handle(array $data): Product
    {
        return DB::transaction(function () use ($data) {

            $mediaItems = json_decode($data['media'] ?? '[]', true) ?: [];

            if (empty($data['product_id'])) {
                throw new \Exception("Product ID is required to attach media.");
            }

            $model = Product::find($data['product_id']);

            if (!$model) {
                throw new \Exception("The targeted Product ID does not exist.");
            }

            $baseDir = "media/products/{$model->id}";

            $currentMaxOrder = $model->media()->max('order');
            $startOrder = is_null($currentMaxOrder) ? 0 : $currentMaxOrder + 1;

            foreach ($mediaItems as $index => $item) {

                if (empty($item['file'])) {
                    continue;
                }

                $tempPath = "temp/{$item['file']}";

                if (!Storage::disk('public')->exists($tempPath)) {
                    continue;
                }

                $absPath   = Storage::disk('public')->path($tempPath);
                $mime      = File::mimeType($absPath);
                $extension = strtolower(pathinfo($item['file'], PATHINFO_EXTENSION));

                $filename  = MediaPath::filename($model->id, $extension);

                $finalPath = "{$baseDir}/files/{$filename}";
                $thumbPath = "{$baseDir}/thumbs/thumb_{$filename}";

                $isImage = in_array($extension, [
                    'jpg', 'jpeg', 'png', 'webp', 'heic'
                ]);

                $thumbnailUrl = null;

                /*
                |--------------------------------------------------------------------------
                | IMAGE PROCESSING (Intervention v4 FIXED)
                |--------------------------------------------------------------------------
                */
                if ($isImage) {

                    try {

                        $img = Image::read($absPath);

                        // Thumbnail
                        $thumb = clone $img;
                        $thumb->scale(width: 600);

                        Storage::disk('public')->put(
                            $thumbPath,
                            $thumb->toJpeg(70)
                        );

                        $thumbnailUrl = $thumbPath;

                        // Optimize original
                        $img->scale(width: 2000);

                        $finalFullPath = Storage::disk('public')->path($finalPath);

                        if (!File::exists(dirname($finalFullPath))) {
                            File::makeDirectory(dirname($finalFullPath), 0755, true);
                        }

                        $img->save($finalFullPath, 85);

                        Storage::disk('public')->delete($tempPath);

                    } catch (\Throwable $e) {

                        \Log::error('Image processing failed: ' . $e->getMessage());

                        Storage::disk('public')->move($tempPath, $finalPath);
                    }

                } else {

                    /*
                    |--------------------------------------------------------------------------
                    | NON-IMAGE FILES
                    |--------------------------------------------------------------------------
                    */
                    Storage::disk('public')->move($tempPath, $finalPath);
                }

                /*
                |--------------------------------------------------------------------------
                | SAVE MEDIA RECORD
                |--------------------------------------------------------------------------
                */
                $model->media()->create([
                    'disk'       => 'public',
                    'path'       => $finalPath,
                    'collection' => $data['collection'] ?? 'product.misc',
                    'status'     => 'ready',
                    'meta'       => json_encode([
                        'original_name' => $item['file'],
                        'size'          => Storage::disk('public')->size($finalPath),
                    ]),
                    'order'      => $startOrder + $index,
                    'tag'        => null,
                    'type'       => $mime,
                    'thumbnail'  => $thumbnailUrl ?? $thumbPath,
                ]);

                /*
                |--------------------------------------------------------------------------
                | QUEUE VIDEO PROCESSING
                |--------------------------------------------------------------------------
                */
                if (!$isImage) {
                    ProcessMediaJob::dispatch($model->media()->latest()->first());
                }
            }

            return $model;
        });
    }
}