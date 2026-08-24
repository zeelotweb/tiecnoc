<?php

use Livewire\Component;
use App\Models\Product;
use App\Services\MetricsService;

new class extends Component {

    public Product $product;

    public ?int $productId = null;
    public ?int $selectedColorId = null;

    /*
    |--------------------------------------------------------------------------
    | LISTEN FOR MODAL TRIGGER
    |--------------------------------------------------------------------------
    */
    protected $listeners = ['load-metrics-tool' => 'loadProduct'];

    // Direct-embed entry point (product edit page tab).
    public function mount($productId = null)
    {
        if ($productId) {
            $this->loadProduct($productId);
        }
    }
    /*
    |--------------------------------------------------------------------------
    | LOAD PRODUCT (FROM JS EVENT)
    |--------------------------------------------------------------------------
    */

    public function loadProduct($id)
    {
        $this->productId = $id;

        $this->product = Product::with('colors.variants')->findOrFail($id);
               // default view = global metrics
            $this->selectedColorId = null;
    }
    /*
    |--------------------------------------------------------------------------
    | SELECT COLOR
    |--------------------------------------------------------------------------
    */
public function selectColor($colorId)
{
    $this->selectedColorId = (int) $colorId;
}

    /*
    |--------------------------------------------------------------------------
    | DATA PROVIDER
    |--------------------------------------------------------------------------
    */
    public function with(MetricsService $metrics)
    {
        if (!isset($this->product)) {
            return [
                'colors' => collect(),
                'data' => [
                    'stock_count' => 0,
                    'stock_value' => 0,
                    'sold_count' => 0,
                    'sold_value' => 0,
                    'favorites' => 0,
                    'views' => 0,
                ],
            ];
        }

        $color = $this->selectedColorId
            ? $this->product->colors->firstWhere('id', $this->selectedColorId)
            : null;

        return [
            'colors' => $this->product->colors,

            'data' => $color
                ? $metrics->getByColor($color)
                : $metrics->getGlobal($this->product),
        ];
    }
};
?>

<div class="space-y-6">

    {{-- COLOR SELECTOR --}}
    <div class="flex gap-2 flex-wrap items-center">
        <button
            wire:click="$set('selectedColorId', null)"
            class="px-3 py-1.5  border text-sm transition-colors
            {{ !$selectedColorId ? 'bg-zinc-900 text-white border-zinc-900 dark:bg-white dark:text-zinc-900 dark:border-white' : 'border-black/15 dark:border-white/15 hover:border-black/40 dark:hover:border-white/40' }}">
            All Colorways
        </button>

        @foreach($colors as $color)
            <button
                wire:click="selectColor({{ $color->id }})"
                title="{{ $color->color_name }}"
                class="w-8 h-8 rounded-full border-2 transition-transform
                {{ $selectedColorId === $color->id ? 'border-[#E31837] scale-110' : 'border-black/15 dark:border-white/15' }}"
                style="background-color: {{ $color->hex_code }}">
            </button>
        @endforeach
    </div>

    {{-- METRICS GRID --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">

        <div class=" border border-black/15 dark:border-white/15 p-4">
            <p class="text-xs text-zinc-500 mb-1">Stock</p>
            <p class="text-xl font-semibold text-zinc-900 dark:text-white">{{ $data['stock_count'] }}</p>
        </div>

        <div class=" border border-black/15 dark:border-white/15 p-4">
            <p class="text-xs text-zinc-500 mb-1">Stock Value</p>
            <p class="text-xl font-semibold text-zinc-900 dark:text-white">${{ number_format($data['stock_value'], 2) }}</p>
        </div>

        <div class=" border border-black/15 dark:border-white/15 p-4">
            <p class="text-xs text-zinc-500 mb-1">Sold</p>
            <p class="text-xl font-semibold text-zinc-900 dark:text-white">{{ $data['sold_count'] }}</p>
        </div>

        <div class=" border border-black/15 dark:border-white/15 p-4">
            <p class="text-xs text-zinc-500 mb-1">Sold Value</p>
            <p class="text-xl font-semibold text-zinc-900 dark:text-white">${{ number_format($data['sold_value'], 2) }}</p>
        </div>

        <div class=" border border-black/15 dark:border-white/15 p-4">
            <p class="text-xs text-zinc-500 mb-1">Favorites</p>
            <p class="text-xl font-semibold text-zinc-900 dark:text-white">{{ $data['favorites'] }}</p>
        </div>

        <div class=" border border-black/15 dark:border-white/15 p-4">
            <p class="text-xs text-zinc-500 mb-1">Views</p>
            <p class="text-xl font-semibold text-zinc-900 dark:text-white">{{ $data['views'] }}</p>
        </div>

    </div>

</div>