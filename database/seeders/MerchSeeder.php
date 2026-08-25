<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductColor;
use App\Services\Admin\ProductContextService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Idempotent — safe to run more than once (including in production):
 * products are matched by slug, colors by (product, color name), variants
 * by (color, size). Re-running only fills in what's missing; it never
 * duplicates a product or regenerates images for a colorway that already
 * has one.
 */
class MerchSeeder extends Seeder
{
    protected ProductContextService $service;

    public function run(): void
    {
        $this->service = app(ProductContextService::class);

        $categoryIds = Category::pluck('id')->all();
        if (empty($categoryIds)) {
            $categoryIds = [Category::create(['name' => 'General', 'slug' => 'general', 'is_active' => true])->id];
        }

        $sizeSet = ['S' => 12, 'M' => 20, 'L' => 18, 'XL' => 0];

        $catalog = [
            // MALE
            [
                'name' => 'Ridge Crewneck', 'gender' => 'male', 'base_price' => 68, 'compare_at_price' => null,
                'material' => 'Cotton Fleece', 'fit_type' => 'Regular',
                'description' => 'A heavyweight crewneck built for daily rotation — brushed fleece interior, ribbed cuffs, no branding games.',
                'colors' => ['Black' => $sizeSet, 'Heather Grey' => $sizeSet, 'Forest' => $sizeSet],
            ],
            [
                'name' => 'Vanguard Hoodie', 'gender' => 'male', 'base_price' => 95, 'compare_at_price' => 120,
                'material' => 'Cotton/Poly Blend', 'fit_type' => 'Oversized',
                'description' => 'Oversized fit, dropped shoulder, kangaroo pocket. The one hoodie that does the heavy lifting.',
                'colors' => ['Black' => $sizeSet, 'Desert Sky' => ['S' => 6, 'M' => 10, 'L' => 8, 'XL' => 4]],
            ],
            [
                'name' => 'Transit Cargo Pants', 'gender' => 'male', 'base_price' => 110, 'compare_at_price' => null,
                'material' => 'Ripstop Cotton', 'fit_type' => 'Relaxed',
                'description' => 'Six-pocket utility cargo built from ripstop cotton. Tapered leg, relaxed through the seat.',
                'colors' => ['Black' => $sizeSet, 'Forest' => ['S' => 5, 'M' => 9, 'L' => 7, 'XL' => 3]],
            ],
            [
                'name' => 'Anchor Flannel Shirt', 'gender' => 'male', 'base_price' => 72, 'compare_at_price' => null,
                'material' => 'Brushed Flannel', 'fit_type' => 'Regular',
                'description' => 'Brushed flannel button-up, weighty enough to wear as an outer layer through fall.',
                'colors' => ['Forest' => $sizeSet, 'Crimson' => ['S' => 5, 'M' => 9, 'L' => 7, 'XL' => 3]],
            ],
            [
                'name' => 'Drift Jogger', 'gender' => 'male', 'base_price' => 78, 'compare_at_price' => 98,
                'material' => 'French Terry', 'fit_type' => 'Tapered',
                'description' => 'Tapered jogger in brushed French terry. Zip pockets, elastic cuff, built for movement.',
                'colors' => ['Black' => $sizeSet, 'Heather Grey' => $sizeSet],
            ],
            [
                'name' => 'Harbor Zip Jacket', 'gender' => 'male', 'base_price' => 115, 'compare_at_price' => null,
                'material' => 'Nylon Shell', 'fit_type' => 'Regular',
                'description' => 'Lightweight nylon shell with a packable hood. Cuts wind, layers over anything.',
                'colors' => ['Black' => ['S' => 6, 'M' => 10, 'L' => 8, 'XL' => 4], 'Cobalt' => ['S' => 5, 'M' => 8, 'L' => 6, 'XL' => 2]],
            ],

            // FEMALE
            [
                'name' => 'Solstice Crop Hoodie', 'gender' => 'female', 'base_price' => 82, 'compare_at_price' => 105,
                'material' => 'French Terry', 'fit_type' => 'Cropped',
                'description' => 'Cropped length, raw hem, French terry construction. Built to layer, easy to live in.',
                'colors' => ['Blush' => $sizeSet, 'Optic White' => $sizeSet, 'Black' => ['S' => 4, 'M' => 8, 'L' => 6, 'XL' => 0]],
            ],
            [
                'name' => 'Aria Ribbed Tank', 'gender' => 'female', 'base_price' => 42, 'compare_at_price' => null,
                'material' => 'Ribbed Cotton', 'fit_type' => 'Slim',
                'description' => 'A ribbed slim tank that layers clean under anything or stands on its own.',
                'colors' => ['Blush' => $sizeSet, 'Black' => $sizeSet, 'Optic White' => $sizeSet],
            ],
            [
                'name' => 'Meridian Wide-Leg Pants', 'gender' => 'female', 'base_price' => 98, 'compare_at_price' => null,
                'material' => 'Cotton Twill', 'fit_type' => 'Wide Leg',
                'description' => 'High-rise, wide leg, cotton twill with real structure. Tailored without trying too hard.',
                'colors' => ['Desert Sky' => $sizeSet, 'Black' => ['S' => 7, 'M' => 11, 'L' => 9, 'XL' => 5]],
            ],
            [
                'name' => 'Lumen Bodysuit', 'gender' => 'female', 'base_price' => 58, 'compare_at_price' => null,
                'material' => 'Ribbed Jersey', 'fit_type' => 'Fitted',
                'description' => 'A fitted ribbed bodysuit that layers under everything and holds its shape all day.',
                'colors' => ['Black' => $sizeSet, 'Optic White' => $sizeSet, 'Blush' => ['S' => 5, 'M' => 9, 'L' => 7, 'XL' => 3]],
            ],
            [
                'name' => 'Cascade Slip Skirt', 'gender' => 'female', 'base_price' => 64, 'compare_at_price' => 85,
                'material' => 'Satin', 'fit_type' => 'Bias Cut',
                'description' => 'Bias-cut satin slip skirt, midi length. Weighted drape, clean seams.',
                'colors' => ['Black' => $sizeSet, 'Blush' => ['S' => 5, 'M' => 8, 'L' => 6, 'XL' => 2]],
            ],
            [
                'name' => 'Haven Puffer Vest', 'gender' => 'female', 'base_price' => 88, 'compare_at_price' => null,
                'material' => 'Recycled Nylon', 'fit_type' => 'Regular',
                'description' => 'Lightweight puffer vest in recycled nylon shell. Packs down, holds real warmth.',
                'colors' => ['Black' => ['S' => 6, 'M' => 10, 'L' => 8, 'XL' => 4], 'Crimson' => ['S' => 4, 'M' => 7, 'L' => 5, 'XL' => 2]],
            ],

            // UNISEX
            [
                'name' => 'Nomad Tee', 'gender' => 'unisex', 'base_price' => 38, 'compare_at_price' => null,
                'material' => '100% Cotton', 'fit_type' => 'Regular',
                'description' => 'The house tee. Midweight cotton, regular fit, built to survive the wash a hundred times over.',
                'colors' => ['Black' => $sizeSet, 'Optic White' => $sizeSet, 'Cobalt' => ['S' => 6, 'M' => 10, 'L' => 8, 'XL' => 4]],
            ],
            [
                'name' => 'Field Jacket', 'gender' => 'unisex', 'base_price' => 135, 'compare_at_price' => 175,
                'material' => 'Waxed Cotton', 'fit_type' => 'Regular',
                'description' => 'Waxed cotton shell, four-pocket field silhouette. Weather-ready, holds its shape for years.',
                'colors' => ['Forest' => ['S' => 4, 'M' => 7, 'L' => 6, 'XL' => 2], 'Black' => ['S' => 3, 'M' => 6, 'L' => 5, 'XL' => 0]],
            ],
            [
                'name' => 'Utility Beanie', 'gender' => 'unisex', 'base_price' => 28, 'compare_at_price' => null,
                'material' => 'Acrylic Knit', 'fit_type' => 'One Size',
                'description' => 'Ribbed knit beanie, unlined. One size, every head.',
                'colors' => ['Black' => ['OS' => 30], 'Crimson' => ['OS' => 22], 'Electric' => ['OS' => 15]],
            ],
            [
                'name' => 'Signal Track Jacket', 'gender' => 'unisex', 'base_price' => 92, 'compare_at_price' => 115,
                'material' => 'Tricot', 'fit_type' => 'Regular',
                'description' => 'Full-zip tricot track jacket with contrast piping. Built for the studio and the street.',
                'colors' => ['Black' => $sizeSet, 'Cobalt' => ['S' => 6, 'M' => 10, 'L' => 8, 'XL' => 4], 'Optic White' => ['S' => 5, 'M' => 8, 'L' => 6, 'XL' => 2]],
            ],
            [
                'name' => 'Basin Shorts', 'gender' => 'unisex', 'base_price' => 48, 'compare_at_price' => null,
                'material' => 'Cotton Twill', 'fit_type' => 'Relaxed',
                'description' => 'Relaxed twill shorts, drawstring waist, deep pockets. Warm-weather staple.',
                'colors' => ['Black' => $sizeSet, 'Forest' => ['S' => 5, 'M' => 9, 'L' => 7, 'XL' => 3]],
            ],
            [
                'name' => 'Ledger Tote Bag', 'gender' => 'unisex', 'base_price' => 54, 'compare_at_price' => null,
                'material' => 'Canvas', 'fit_type' => 'One Size',
                'description' => 'Heavyweight canvas tote, reinforced base, interior pocket. Built to actually carry things.',
                'colors' => ['Black' => ['OS' => 25], 'Optic White' => ['OS' => 18]],
            ],
        ];

        $palette = [
            'Black' => '#000000', 'Optic White' => '#FFFFFF', 'Desert Sky' => '#002451',
            'Heather Grey' => '#9CA3AF', 'Crimson' => '#B91C1C', 'Electric' => '#EAB308',
            'Blush' => '#F472B6', 'Forest' => '#14532D', 'Cobalt' => '#1D4ED8',
        ];

        foreach ($catalog as $i => $entry) {
            $slug = Str::slug($entry['name']);

            $product = Product::firstOrCreate(
                ['slug' => $slug],
                [
                    'name' => $entry['name'],
                    'gender' => $entry['gender'],
                    'base_price' => $entry['base_price'],
                    'compare_at_price' => $entry['compare_at_price'],
                    'category_id' => $categoryIds[$i % count($categoryIds)],
                    'description' => $entry['description'],
                    'material' => $entry['material'],
                    'fit_type' => $entry['fit_type'],
                    'status' => 'draft',
                ]
            );

            foreach ($entry['colors'] as $colorName => $sizes) {
                $color = ProductColor::where('product_id', $product->id)
                    ->where('color_name', $colorName)
                    ->first();

                if (! $color) {
                    $hex = $palette[$colorName] ?? '#111111';
                    $front = $this->placeholderImage($product->id, $colorName, 'front', $hex);
                    $back = $this->placeholderImage($product->id, $colorName, 'back', $hex);

                    $color = $this->service->attachUploadedColorImages(
                        $product->id, $colorName, $hex, $front, $back
                    );
                }

                foreach ($sizes as $size => $stock) {
                    $this->service->upsertVariant($color->id, $size, null, $stock);
                }

                if ($color->status !== 'live') {
                    $this->service->toggleColorStatus($color);
                }
            }

            $this->command?->info("Seeded: {$entry['name']} ({$entry['gender']})");
        }
    }

    protected function placeholderImage(int $productId, string $colorName, string $side, string $hex): string
    {
        $slug = Str::slug($colorName);
        $path = "media/colors/{$productId}/{$slug}-{$side}-" . Str::random(8) . '.jpg';
        $fullPath = Storage::disk('public')->path($path);

        File::ensureDirectoryExists(dirname($fullPath));

        [$r, $g, $b] = sscanf($hex, '#%02x%02x%02x');
        $im = imagecreatetruecolor(800, 1000);
        imagefill($im, 0, 0, imagecolorallocate($im, $r, $g, $b));

        // subtle band so front/back are visually distinguishable in the thumbnail rail
        if ($side === 'back') {
            $shade = imagecolorallocatealpha($im, 0, 0, 0, 70);
            imagefilledrectangle($im, 0, 820, 800, 1000, $shade);
        }

        imagejpeg($im, $fullPath, 85);
        imagedestroy($im);

        return $path;
    }
}
