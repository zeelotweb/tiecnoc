<?php

namespace App\Services\Store;

use App\Models\Product;

class StorefrontService
{
    /**
     * MASTER CATALOGUE QUERY
     * Injects ActivityService to handle Pulse Scores natively.
     */
    public function getCatalogue(string $gender = null, string $categorySlug = null, bool $onlySales = false)
    {
        $activity = app(ActivityService::class);

        return Product::query()
            ->where('is_active', true) 
            ->with(['category', 'reactions'])
            ->when($gender, fn($q) => $q->where('gender', $gender))
            ->when($categorySlug, function ($query) use ($categorySlug) {
                return $query->whereHas('category', fn($q) => $q->where('slug', $categorySlug));
            })
            ->when($onlySales, fn($q) => $q->where('is_featured', true))
            ->latest()
            ->get()
            ->map(function ($product) use ($activity) {
                // Use the centralized ActivityService for the score
                $product->pulse_score = $activity->calculateScore($product);
                return $product;
            });
    }

    public function getProductDetails(string $slug)
    {
        return Product::where('slug', $slug)
            ->with(['variants.attributeValues.attribute', 'category', 'reactions'])
            ->firstOrFail();
    }
}
