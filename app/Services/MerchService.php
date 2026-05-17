<?php

namespace App\Services;

use App\Models\{Product, ProductColor, ProductVariant, Media};

class MerchService
{
    /*
    |--------------------------------------------------------------------------
    | BASE RELATIONS
    |--------------------------------------------------------------------------
    */
    protected function baseRelations()
    {
        return [
            'colors' => function ($q) {
                $q->where('status', 'live')
                  ->select(
                      'id',
                      'product_id',
                      'color_name',
                      'hex_code',
                      'front_image_path',
                      'back_image_path',
                      'status'
                  )
                  ->orderBy('id');
            },

            'colors.variants' => function ($q) {
                $q->select(
                        'id',
                        'product_color_id',
                        'sku',
                        'size',
                        'price',
                        'stock_quantity'
                    )
                  ->orderBy('size');
            }
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | DISCOVERY FEED (GRID / HOMEPAGE)
    |--------------------------------------------------------------------------
    */
    public function getDiscoveryFeed($limit = 5)
    {
        return Product::whereHas('colors', function ($q) {
                $q->where('status', 'live');
            })
            ->select('id', 'name', 'slug', 'base_price', 'description', 'gender')
            ->with($this->baseRelations())
            ->take($limit)
            ->get();
    }

    /*
    |--------------------------------------------------------------------------
    | PRODUCT DETAIL PAGE (PDP)
    |--------------------------------------------------------------------------
    */
    public function getProductBySlug($slug)
    {
        return Product::where('slug', $slug)
            ->whereHas('colors', fn ($q) => $q->where('status', 'live'))
            ->with(array_merge(['category'], $this->baseRelations()))
            ->firstOrFail();
    }

    /*
    |--------------------------------------------------------------------------
    | COLOR CONTEXT (ADMIN + FRONT)
    |--------------------------------------------------------------------------
    */
    public function getActiveColorContexts($productId)
    {
        return ProductColor::where('product_id', $productId)
            ->where('status', 'live')
            ->select(
                'id',
                'product_id',
                'color_name',
                'hex_code',
                'front_image_path',
                'back_image_path',
                'status'
            )
            ->orderBy('id')
            ->get();
    }

    /*
    |--------------------------------------------------------------------------
    | FEATURED PRODUCT (GENDER ORIENTED)
    |--------------------------------------------------------------------------
    */
    public function getFeaturedByGender(string $gender)
    {
        return Product::where('gender', $gender)
            ->whereHas('colors', fn ($q) => $q->where('status', 'live'))
            ->with($this->baseRelations())
            ->inRandomOrder()
            ->first();
    }

    /*
    |--------------------------------------------------------------------------
    | CATALOGUE
    |--------------------------------------------------------------------------
    */
    public function getCatalogueByGender($perPage, $gender = 'all')
    {
        return Product::whereHas('colors', function ($q) {
                $q->where('status', 'live');
            })
            ->select('id', 'name', 'slug', 'base_price', 'description', 'gender')
            ->when($gender !== 'all', fn ($query) => $query->where('gender', $gender))
            ->with($this->baseRelations())
            ->paginate($perPage);
    }

    /*
    |--------------------------------------------------------------------------
    | DISPLAY HELPER (PIPELINE)
    |--------------------------------------------------------------------------
    */
    public function getDisplayData(Product $product)
    {
        $color = $product->colors
            ->where('status', 'live')
            ->sortByDesc(fn ($c) => !empty($c->front_image_path))
            ->first();

        if (!$color) {
            return [
                'color'   => null,
                'variant' => null,
                'price'   => $product->base_price,
                'image'   => null,
                'back'    => null,
            ];
        }

        $variant = $color->variants
            ->sortByDesc(fn ($v) => $v->price ?? 0)
            ->first();

        return [
            'color'   => $color,
            'variant' => $variant,
            'price'   => $variant?->price ?? $product->base_price,
            'image'   => $color->front_image_path,
            'back'    => $color->back_image_path,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | PROMOTIONAL MEDIA (HERO/GALLERY)
    |--------------------------------------------------------------------------
    */
    public function getRandomPromotionalMedia($product)
    {
        if (!$product) return null;

        return Media::where('mediable_type', 'product')
            ->where('mediable_id', $product->id)
            ->where('collection', 'product.misc')
            ->inRandomOrder()
            ->first();
    }

    /*
    |--------------------------------------------------------------------------
    | RANDOM SELLABLE PRODUCT (LANDING / HERO / GALLERY)
    |--------------------------------------------------------------------------
    */
    public function getRandomSellableProduct()
    {
        return Product::whereHas('colors', function ($q) {
                $q->where('status', 'live')
                  ->whereHas('variants');
            })
            ->with($this->baseRelations())
            ->inRandomOrder()
            ->first();
    }

    /*
    |--------------------------------------------------------------------------
    | GALLERY CONTEXT (SINGLE SOURCE OF TRUTH)
    |--------------------------------------------------------------------------
    */
    public function getGalleryContext()
    {
        $product = $this->getRandomSellableProduct();

        if (!$product) {
            return [
                'product' => null,
                'promo'   => null,
            ];
        }

        return [
            'product' => $product,
            'promo'   => $this->getRandomPromotionalMedia($product),
        ];
    }



    /*
    |--------------------------------------------------------------------------
    | GALLERY for collections CONTEXT (SINGLE SOURCE OF TRUTH)
    |--------------------------------------------------------------------------
    */

        public function getRelatedMerch(Product $product, int $limit = 6)
        {
            return Product::where('id', '!=', $product->id)

                // same category as PDP product
                ->where('category_id', $product->category_id)

                // only products that have at least one LIVE color
                ->whereHas('colors', function ($q) {
                    $q->where('status', 'live');
                })

                ->with($this->baseRelations())

                ->inRandomOrder()

                ->take($limit)

                ->get();
        }

}