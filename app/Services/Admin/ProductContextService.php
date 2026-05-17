<?php

namespace App\Services\Admin;

use App\Models\Product;
use App\Models\ProductColor;
use App\Models\ProductVariant;
use App\Models\Media;
use Illuminate\Support\Str;

class ProductContextService
{
    /*
    |--------------------------------------------------------------------------
    | PRODUCT COLOR RESOLUTION
    | (Get or create a color tied to a product)
    |--------------------------------------------------------------------------
    */
    public function getOrCreateColor(
        int $productId,
        string $colorName,
        ?string $hex = null
    ): ProductColor {
        return ProductColor::firstOrCreate(
            [
                'product_id' => $productId,
                'color_name' => $colorName,
            ],
            [
                'hex_code' => $hex,
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | GET SINGLE VALID PRODUCT COLOR
    | (Ensures color belongs to product safely)
    |--------------------------------------------------------------------------
    */

    public function getColor(int $productId, int $colorId): ?ProductColor
    {
        return ProductColor::where('id', $colorId)
            ->where('product_id', $productId)
            ->first(); // MUST be first()
    }

    /*
    |--------------------------------------------------------------------------
    | GET ALL COLORS FOR PRODUCT
    | (Used across admin tools for selection + UI)
    |--------------------------------------------------------------------------
    */
    public function getProductColors(int $productId)
    {


    if (! $productId) {
        return collect();
    }
        return ProductColor::where('product_id', $productId)
            ->orderBy('created_at')
            ->get();
    }


    /*
    |--------------------------------------------------------------------------
    | COLOR MEDIA SYNC (FRONT / BACK)
    | (Replaces media + updates denormalized color paths)
    |--------------------------------------------------------------------------
    */
    public function syncColorMedia(
        ProductColor $color,
        string $side,
        string $path
    ): void {

        Media::where([
            'mediable_id'   => $color->id,
            'mediable_type' => ProductColor::class,
            'collection'    => "color.{$side}",
        ])->delete();

        Media::create([
            'mediable_id'   => $color->id,
            'mediable_type' => ProductColor::class,
            'path'          => $path,
            'collection'    => "color.{$side}",
            'disk'          => 'public',
            'status'        => 'ready',
            'type'          => 'image',
        ]);

        $color->update([
            "{$side}_image_path" => $path,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | ATTACH COLOR ASSETS (ORCHESTRATION)
    | (Creates color + syncs front/back images)
    |--------------------------------------------------------------------------
    */
    public function attachColorAssets(
        int $productId,
        string $colorName,
        ?string $hex,
        ?string $frontPath,
        ?string $backPath
    ): ProductColor {

        $color = $this->getOrCreateColor($productId, $colorName, $hex);

        foreach ([
            'front' => $frontPath,
            'back'  => $backPath,
        ] as $side => $path) {
            if (!$path) continue;

            $this->syncColorMedia($color, $side, $path);
        }

        return $color;
    }

    /*
    |--------------------------------------------------------------------------
    | COLOR READINESS CHECK (BUSINESS RULE)
    | (Used for toggle eligibility / go-live rules)
    |--------------------------------------------------------------------------
    */
    public function isColorReady(ProductColor $color): bool
    {
        return !empty($color->front_image_path)
            && $color->variants()->count() > 0;
    }

    /*
    |--------------------------------------------------------------------------
    | VARIANT UPSERT (SIZE / PRICE / STOCK)
    | (Core spec system for product colors)
    |--------------------------------------------------------------------------
    */
    public function upsertVariant(
        int $colorId,
        string $size,
        ?float $price,
        int $stock = 0
    ): ProductVariant {

        return ProductVariant::updateOrCreate(
            [
                'product_color_id' => $colorId,
                'size'             => strtoupper($size),
            ],
            [
                'sku'            => 'TNC-' . strtoupper(Str::random(6)),
                'price'          => $price ?: null,
                'stock_quantity' => $stock,
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | GET VARIANTS FOR COLOR
    | (Used in specs tool UI + analytics)
    |--------------------------------------------------------------------------
    */
    public function getColorVariants(int $colorId)
    {
        return ProductVariant::where('product_color_id', $colorId)
            ->orderBy('size')
            ->get();
    }



/*
|--------------------------------------------------------------------------
| ATTACH UPLOADED COLOR IMAGES
| (Creates/resolves color and syncs front/back images from uploads)
|--------------------------------------------------------------------------
*/


    public function attachUploadedColorImages(
    int $productId,
    string $colorName,
    ?string $hex,
    ?string $frontPath,
    ?string $backPath
): ProductColor {

    $color = $this->getOrCreateColor($productId, $colorName, $hex);

    foreach ([
        'front' => $frontPath,
        'back'  => $backPath,
    ] as $side => $path) {
        if (!$path) continue;

        $this->syncColorMedia($color, $side, $path);
    }

    return $color;
}




















   /*
    |--------------------------------------------------------------------------
    | COLOR TOGGLE MAP (UI READY PAYLOAD)
    | Builds structured data for toggle modal display
    |--------------------------------------------------------------------------
    */
    public function getColorToggleMap(Product $product): array
    {
        return $product->colors->map(function ($color) {

            $hasImage = !empty($color->front_image_path);
            $hasVariants = $color->variants->count() > 0;

            return [
                'id' => $color->id,
                'name' => $color->color_name,
                'status' => $color->status ?? 'draft',

                'ready' => $hasImage && $hasVariants,

                'missing' => [
                    'image' => !$hasImage,
                    'variants' => !$hasVariants,
                ],
            ];
        })->toArray();
    }

    /*
    |--------------------------------------------------------------------------
    | COLOR READINESS CHECK (BUSINESS RULE)
    | Single source of truth for toggle eligibility
    |--------------------------------------------------------------------------
    */
    public function canToggleColor(ProductColor $color): bool
    {
        $hasImage = !empty($color->front_image_path);
        $hasVariants = $color->variants()->count() > 0;

        return $hasImage && $hasVariants;
    }

    /*
    |--------------------------------------------------------------------------
    | TOGGLE COLOR STATUS
    | Switches draft ↔ live safely
    |--------------------------------------------------------------------------
    */
    public function toggleColorStatus(ProductColor $color): ProductColor
    {
        $color->status = $color->status === 'live' ? 'draft' : 'live';
        $color->save();

        return $color;
    }
}







