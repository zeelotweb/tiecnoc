<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'order_number',
        'user_id',
        'status',
        'total_amount',
        'stripe_session_id',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Unified Cart Resolver (used everywhere)
     */
    public static function currentCart()
    {
        return static::where('status', 'pending')
            ->whereNull('stripe_session_id')
            ->where(function ($q) {
                if (auth()->check()) {
                    $q->where('user_id', auth()->id());
                } else {
                    $q->where('metadata->session_id', session()->getId());
                }
            })
            ->first();
    }
}