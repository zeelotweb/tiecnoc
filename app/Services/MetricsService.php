<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductColor;
use Illuminate\Support\Facades\DB;

class MetricsService
{
    /*
    |--------------------------------------------------------------------------
    | GLOBAL METRICS
    |--------------------------------------------------------------------------
    */
    public function getGlobal(Product $product): array
    {
        $product->loadMissing('colors.variants.product');

        $totals = [
            'stock_count' => 0,
            'stock_value' => 0,
            'sold_count' => 0,
            'sold_value' => 0,
            'favorites' => 0,
            'views' => 0, // safe default
        ];

        foreach ($product->colors as $color) {
            $data = $this->getByColor($color);

            foreach ($totals as $key => $value) {
                $totals[$key] += $data[$key];
            }
        }

        return $totals;
    }

    /*
    |--------------------------------------------------------------------------
    | COLOR METRICS
    |--------------------------------------------------------------------------
    */
    public function getByColor(ProductColor $color): array
    {
        $color->loadMissing('variants.product');

        $variants = $color->variants;

        $basePrice = $color->product?->base_price ?? 0;

        $stockCount = $variants->sum('stock_quantity');

        $stockValue = $variants->sum(function ($v) use ($basePrice) {
            return $v->stock_quantity * ($v->price ?? $basePrice);
        });

        $variantIds = $variants->pluck('id');

        /*
        |--------------------------------------------------------------------------
        | SOLD DATA
        |--------------------------------------------------------------------------
        */
        $soldCount = DB::table('order_items')
            ->whereIn('product_variant_id', $variantIds)
            ->sum('qty');

        $soldValue = DB::table('order_items')
            ->whereIn('product_variant_id', $variantIds)
            ->selectRaw('SUM(price * qty) as total')
            ->value('total') ?? 0;

        /*
        |--------------------------------------------------------------------------
        | FAVORITES
        |--------------------------------------------------------------------------
        */
        $favorites = DB::table('reactions')
            ->where('type', 'favorite')
            ->whereIn('product_variant_id', $variantIds)
            ->count();

        /*
        |--------------------------------------------------------------------------
        | VIEWS (SAFE FALLBACK - TABLE OPTIONAL)
        |--------------------------------------------------------------------------
        */
        $views = 0;

        if (DB::getSchemaBuilder()->hasTable('views')) {
            $views = DB::table('views')
                ->where('product_id', $color->product_id)
                ->count();
        }

        return [
            'stock_count' => $stockCount,
            'stock_value' => $stockValue,
            'sold_count' => $soldCount,
            'sold_value' => $soldValue,
            'favorites' => $favorites,
            'views' => $views,
        ];
    }
}