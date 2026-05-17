<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_variant_id',
        'name',
        'sku',
        'image',
        'attr',
        'qty',
        'price',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function variant()
    {
        return $this->belongsTo(\App\Models\ProductVariant::class, 'product_variant_id');
    }
    
}