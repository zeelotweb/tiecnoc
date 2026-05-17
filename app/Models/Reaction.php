<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reaction extends Model
{
    protected $fillable = [
        'user_id',
        'session_id',
        'product_variant_id',
        'type',
    ];

public function variant()
{
    return $this->belongsTo(\App\Models\ProductVariant::class, 'product_variant_id');
}

    
}
