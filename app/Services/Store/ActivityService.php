<?php

namespace App\Services\Store;

use App\Models\Product;
use App\Models\Order;
use Illuminate\Support\Collection;

class ActivityService
{
    /**
     * TNC / LEDGER: OWNERSHIP CHECK
     * Determines if the user has a 'paid' order containing this product.
     */
    public function owns(Product $product): bool
    {
        if (!auth()->check()) return false;

        return Order::where('user_id', auth()->id())
            ->where('status', 'paid')
            ->whereHas('items', function ($query) use ($product) {
                $query->where('product_id', $product->id);
            })
            ->exists();
    }

    /**
     * TNC / LEDGER: OWNED IDs
     * Useful for marking "ARCHIVED" on catalogue grids.
     */
    public function getOwnedIds(): Collection
    {
        if (!auth()->check()) return collect();

        return auth()->user()->orders()
            ->where('status', 'paid')
            ->with('items')
            ->get()
            ->pluck('items.*.product_id')
            ->flatten()
            ->unique();
    }
}
