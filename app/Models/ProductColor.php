<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class ProductColor extends Model
{
    protected $fillable = [
        'product_id',
        'color_name',
        'hex_code',
        'front_image_path',
        'back_image_path',
        'status', // ✅ REQUIRED
    ];

    /*
    |--------------------------------------------------------------------------
    | PARENT PRODUCT
    |--------------------------------------------------------------------------
    */
    public function product()
    {
        return $this->belongsTo(\App\Models\Product::class);
    }

    /*
    |--------------------------------------------------------------------------
    | VARIANTS (SPECS LAYER)
    |--------------------------------------------------------------------------
    */
    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class, 'product_color_id');
    }

    /*
    |--------------------------------------------------------------------------
    | MEDIA (SOURCE OF TRUTH FOR IMAGES)
    |--------------------------------------------------------------------------
    */
    public function media(): MorphMany
    {
        return $this->morphMany(\App\Models\Media::class, 'mediable');
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSOR: SAFE HEX FALLBACK
    |--------------------------------------------------------------------------
    */
    public function getResolvedHexAttribute()
    {
        return $this->hex_code ?? '#E5E7EB';
    }
}