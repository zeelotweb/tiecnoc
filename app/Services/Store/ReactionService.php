<?php

namespace App\Services\Store;

use App\Models\Reaction;
use Illuminate\Database\Eloquent\Collection;

class ReactionService
{
    /*
    |--------------------------------------------------------------------------
    | BASE QUERY (USER / GUEST SAFE)
    |--------------------------------------------------------------------------
    */
    protected function baseQuery()
    {
        return Reaction::where(function ($q) {
            if (auth()->check()) {
                $q->where('user_id', auth()->id());
            } else {
                $q->where('session_id', session()->getId());
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | TOGGLE REACTION
    |--------------------------------------------------------------------------
    */
    public function toggle($variantId, $type)
    {
        $existing = $this->baseQuery()
            ->where('product_variant_id', $variantId)
            ->where('type', $type)
            ->first();

        if ($existing) {
            $existing->delete();
            return false;
        }

        Reaction::create([
            'user_id' => auth()->id(),
            'session_id' => session()->getId(),
            'product_variant_id' => $variantId,
            'type' => $type,
        ]);

        return true;
    }

    /*
    |--------------------------------------------------------------------------
    | FETCH REACTIONS (FAVORITES ETC)
    |--------------------------------------------------------------------------
    */
    public function getByType(string $type = 'favorite'): Collection
    {
        return $this->baseQuery()
            ->where('type', $type)
            ->with([
                'variant.color.product', // ✅ full chain
            ])
            ->latest()
            ->get();
    }

    /*
    |--------------------------------------------------------------------------
    | EXISTS CHECK
    |--------------------------------------------------------------------------
    */
    public function exists($variantId, $type): bool
    {
        return $this->baseQuery()
            ->where('product_variant_id', $variantId)
            ->where('type', $type)
            ->exists();
    }
}