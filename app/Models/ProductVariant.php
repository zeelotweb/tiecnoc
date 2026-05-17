<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_color_id',
        'sku',
        'size',
        'price',
        'stock_quantity',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    public function color(): BelongsTo
    {
        return $this->belongsTo(ProductColor::class, 'product_color_id');
    }


    public function product()
    {
        return $this->hasOneThrough(
            Product::class,
            ProductColor::class,
            'id',              // ProductColor.id
            'id',              // Product.id
            'product_color_id', // Variant FK → Color
            'product_id'        // Color FK → Product
        );
    }
    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

    // ✅ safe product access
    public function getProductAttribute()
    {
        return $this->color?->product;
    }

    public function getDisplayPriceAttribute()
    {
        return $this->price
            ?? $this->color?->product?->base_price;
    }

    public function getColorNameAttribute()
    {
        return $this->color?->color_name;
    }

    public function getHexCodeAttribute()
    {
        return $this->color?->hex_code;
    }
}