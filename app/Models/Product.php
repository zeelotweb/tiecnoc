<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'material',
        'fit_type',
        'care_instructions',
        'gender',
        'base_price',
        'compare_at_price',
        'sku_prefix',
        'category_id',
        'status',
        'is_featured'
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'compare_at_price' => 'decimal:2',
        'is_featured' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function category(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Category::class);
    }

    /**
     * ✅ CORE RELATION (NEW ARCHITECTURE)
     * Product → Colors
     */
    public function colors(): HasMany
    {
        return $this->hasMany(\App\Models\ProductColor::class);
    }

    /**
     * Optional: Access variants THROUGH colors (if ever needed)
     */
    public function variants()
    {
        return $this->hasManyThrough(
            \App\Models\ProductVariant::class,
            \App\Models\ProductColor::class,
            'product_id',        // FK on colors
            'product_color_id',  // FK on variants
            'id',                // local key on products
            'id'                 // local key on colors
        );
    }

    public function media(): MorphMany
    {
        return $this->morphMany(\App\Models\Media::class, 'mediable');
    }
}