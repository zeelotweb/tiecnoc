<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;
use App\Models\Media;

class ProcessMedia implements ShouldQueue
{
    use Dispatchable, Queueable, SerializesModels;

    public function __construct(
        public string $modelType,
        public int $modelId,
        public string $tempId,
        public string $collection
    ) {}

    public function handle(): void
    {
        $model = ($this->modelType)::find($this->modelId);
        if (!$model) return;

        $disk = Storage::disk('public');
        $tempPath = "temp/{$this->tempId}";

        if (!$disk->exists($tempPath)) return;

        $ext = strtolower(pathinfo($tempPath, PATHINFO_EXTENSION));
        $uuid = (string) Str::uuid();

        // Determine directory
        $baseDir = str_contains($this->modelType, 'Variant')
            ? "variants/{$this->modelId}"
            : "products/{$this->modelId}";

        if (!$disk->exists($baseDir)) {
            $disk->makeDirectory($baseDir);
        }

        // PROCESS FILE
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {

            $img = Image::read($disk->path($tempPath))->orient();

            $finalPath = "{$baseDir}/{$uuid}.webp";

            $disk->put(
                $finalPath,
                (string) $img->scale(width: 2000)->toWebp(85)
            );

        } else {

            $finalPath = "{$baseDir}/{$uuid}.{$ext}";
            $disk->move($tempPath, $finalPath);
        }

        // Handle single-slot collections
        if ($this->isSingleSlot($this->collection)) {
            Media::where('mediable_type', $this->modelType)
                ->where('mediable_id', $this->modelId)
                ->where('collection', $this->collection)
                ->delete();
        }

        // Save record
        Media::create([
            'mediable_type' => $this->modelType,
            'mediable_id'   => $this->modelId,
            'disk'          => 'public',
            'path'          => $finalPath,
            'collection'    => $this->collection,
            'status'        => 'ready',
            'meta'          => json_encode([
                'original_ext' => $ext,
                'uuid' => $uuid
            ])
        ]);

        // Cleanup
        $disk->delete($tempPath);
    }

    protected function isSingleSlot(string $collection): bool
    {
        return in_array($collection, [
            'product.cover.front',
            'product.cover.back',
            'variant.front',
            'variant.back',
        ]);
    }
}