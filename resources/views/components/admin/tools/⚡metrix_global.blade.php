<?php

use Livewire\Component;
use App\Models\Product;
use App\Models\ProductColor;
use App\Models\ProductVariant;
use App\Models\Reaction;

new class extends Component {

    public function with(): array
    {
        /*
        |--------------------------------------------------------------------------
        | BASE COUNTS
        |--------------------------------------------------------------------------
        */
        $products = Product::count();

        /*
        |--------------------------------------------------------------------------
        | PRODUCTS BY GENDER (ONLY SELLABLE)
        |--------------------------------------------------------------------------
        */
        $productsByGender = Product::selectRaw('gender, COUNT(*) as total')
            ->whereHas('colors', fn ($q) => $q->where('status', 'live'))
            ->groupBy('gender')
            ->pluck('total', 'gender');

        /*
        |--------------------------------------------------------------------------
        | COLORS (GLOBAL + LIVE)
        |--------------------------------------------------------------------------
        */
        $colorsTotal = ProductColor::count();
        $colorsLive  = ProductColor::where('status', 'live')->count();

        /*
        |--------------------------------------------------------------------------
        | COLORS BY GENDER
        |--------------------------------------------------------------------------
        */
        $colorsByGender = ProductColor::selectRaw('products.gender, COUNT(*) as total')
            ->join('products', 'products.id', '=', 'product_colors.product_id')
            ->where('product_colors.status', 'live')
            ->groupBy('products.gender')
            ->pluck('total', 'gender');

        /*
        |--------------------------------------------------------------------------
        | VARIANTS
        |--------------------------------------------------------------------------
        */
        $variantsTotal = ProductVariant::count();

        $variantsByGender = ProductVariant::selectRaw('products.gender, COUNT(*) as total')
            ->join('product_colors', 'product_colors.id', '=', 'product_variants.product_color_id')
            ->join('products', 'products.id', '=', 'product_colors.product_id')
            ->groupBy('products.gender')
            ->pluck('total', 'gender');

        /*
        |--------------------------------------------------------------------------
        | COLORS PER PRODUCT (AVG)
        |--------------------------------------------------------------------------
        */
        $avgColorsPerProduct = ProductColor::selectRaw('AVG(cnt) as avg')
            ->fromSub(function ($q) {
                $q->from('product_colors')
                  ->selectRaw('product_id, COUNT(*) as cnt')
                  ->groupBy('product_id');
            }, 'sub')
            ->value('avg');

        /*
        |--------------------------------------------------------------------------
        | FAVORITES (REACTIONS)
        |--------------------------------------------------------------------------
        */
        $favoritesTotal = Reaction::where('type', 'favorite')->count();

        $favoritesByGender = Reaction::selectRaw('products.gender, COUNT(*) as total')
            ->join('product_variants', 'product_variants.id', '=', 'reactions.product_variant_id')
            ->join('product_colors', 'product_colors.id', '=', 'product_variants.product_color_id')
            ->join('products', 'products.id', '=', 'product_colors.product_id')
            ->where('reactions.type', 'favorite')
            ->groupBy('products.gender')
            ->pluck('total', 'gender');

        return [
            'products' => $products,

            'productsByGender' => $productsByGender,

            'colorsTotal' => $colorsTotal,
            'colorsLive'  => $colorsLive,
            'colorsByGender' => $colorsByGender,

            'variantsTotal' => $variantsTotal,
            'variantsByGender' => $variantsByGender,

            'avgColorsPerProduct' => round($avgColorsPerProduct, 2),

            'favoritesTotal' => $favoritesTotal,
            'favoritesByGender' => $favoritesByGender,
        ];
    }
};
?>

<div class="max-w-7xl mx-auto p-6 lg:p-10 space-y-10 font-sans">

    {{-- HEADER --}}
    <div class="border-b-4 border-black dark:border-white pb-4">
        <h1 class="text-xl md:text-2xl font-black uppercase tracking-widest italic">
            Global Shop Matrix
        </h1>
    </div>

    {{-- GRID --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">

        {{-- PRODUCTS --}}
        <div class="p-4 border border-black dark:border-white">
            <p class="text-[10px] uppercase opacity-40">Total Products</p>
            <p class="text-2xl font-black">{{ $products }}</p>
        </div>

        {{-- COLORS --}}
        <div class="p-4 border border-black dark:border-white">
            <p class="text-[10px] uppercase opacity-40">Total Colors</p>
            <p class="text-2xl font-black">{{ $colorsTotal }}</p>
        </div>

        <div class="p-4 border border-black dark:border-white">
            <p class="text-[10px] uppercase opacity-40">Live Colors</p>
            <p class="text-2xl font-black text-emerald-500">{{ $colorsLive }}</p>
        </div>

        {{-- VARIANTS --}}
        <div class="p-4 border border-black dark:border-white">
            <p class="text-[10px] uppercase opacity-40">Variants</p>
            <p class="text-2xl font-black">{{ $variantsTotal }}</p>
        </div>

        {{-- AVG COLORS --}}
        <div class="p-4 border border-black dark:border-white col-span-2">
            <p class="text-[10px] uppercase opacity-40">Avg Colors / Product</p>
            <p class="text-2xl font-black">{{ $avgColorsPerProduct }}</p>
        </div>

        {{-- FAVORITES --}}
        <div class="p-4 border border-black dark:border-white col-span-2">
            <p class="text-[10px] uppercase opacity-40">Total Favorites</p>
            <p class="text-2xl font-black text-pink-500">{{ $favoritesTotal }}</p>
        </div>

    </div>

    {{-- BREAKDOWN --}}
    <div class="grid md:grid-cols-3 gap-10">

        {{-- PRODUCTS BY GENDER --}}
        <div>
            <h2 class="text-xs uppercase font-black tracking-widest mb-4">Products by Gender</h2>
            @foreach(['male','female','unisex'] as $g)
                <div class="flex justify-between border-b py-2 text-sm">
                    <span class="uppercase">{{ $g }}</span>
                    <span>{{ $productsByGender[$g] ?? 0 }}</span>
                </div>
            @endforeach
        </div>

        {{-- COLORS BY GENDER --}}
        <div>
            <h2 class="text-xs uppercase font-black tracking-widest mb-4">Colors by Gender</h2>
            @foreach(['male','female','unisex'] as $g)
                <div class="flex justify-between border-b py-2 text-sm">
                    <span class="uppercase">{{ $g }}</span>
                    <span>{{ $colorsByGender[$g] ?? 0 }}</span>
                </div>
            @endforeach
        </div>

        {{-- VARIANTS BY GENDER --}}
        <div>
            <h2 class="text-xs uppercase font-black tracking-widest mb-4">Variants by Gender</h2>
            @foreach(['male','female','unisex'] as $g)
                <div class="flex justify-between border-b py-2 text-sm">
                    <span class="uppercase">{{ $g }}</span>
                    <span>{{ $variantsByGender[$g] ?? 0 }}</span>
                </div>
            @endforeach
        </div>

    </div>

    {{-- FAVORITES BY GENDER --}}
    <div>
        <h2 class="text-xs uppercase font-black tracking-widest mb-4">Favorites by Gender</h2>

        <div class="grid md:grid-cols-3 gap-4">
            @foreach(['male','female','unisex'] as $g)
                <div class="p-4 border border-black dark:border-white text-center">
                    <p class="text-[10px] uppercase opacity-40">{{ $g }}</p>
                    <p class="text-xl font-black text-pink-500">
                        {{ $favoritesByGender[$g] ?? 0 }}
                    </p>
                </div>
            @endforeach
        </div>
    </div>

</div>