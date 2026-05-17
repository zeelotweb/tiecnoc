<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Media extends Model
{
    protected $fillable = [
        'mediable_type',
        'mediable_id',
        'disk',
        'path',
        'collection',
        'status',
        'meta',
        'order',
        'tag',
        'type',        // 🔥 Locked and loaded
        'thumbnail'    // 🔥 Locked and loaded
    ];

    protected $casts = [
        'meta' => 'array'
    ];

    public function mediable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Determine if file points to a physical image.
     */
    public function isImage()
    {
        // ⚡ Safely handles both 'image/png' and fallback 'image' strings
        return str_starts_with($this->type, 'image');
    }

    /**
     * Determine if file points to a physical video.
     */
    public function isVideo()
    {
        // ⚡ Safely handles both 'video/mp4' and fallback 'video' strings
        return str_starts_with($this->type, 'video');
    }

    /**
     * Determine if file points to a PDF.
     */
    public function isPdf()
    {
        return $this->type === 'application/pdf' || str_ends_with($this->path, '.pdf');
    }
}
