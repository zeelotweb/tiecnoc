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

<div class="p-6 space-y-6">

    {{-- COLOR SELECTOR --}}
    <div class="flex gap-3 flex-wrap">

        <button 
            wire:click="$set('selectedColorId', null)"
            class="px-3 py-2 border text-xs font-black uppercase 
            {{ !$selectedColorId ? 'bg-black text-white' : '' }}">
            Global
        </button>

        @foreach($colors as $color)
            <button 
                wire:click="selectColor({{ $color->id }})"
                class="w-10 h-10 border-2 transition-transform
                {{ $selectedColorId === $color->id ? 'border-red-500 scale-110' : 'border-black/30' }}"
                style="background-color: {{ $color->hex_code }}">
            </button>
        @endforeach

    </div>

    {{-- METRICS GRID --}}
    <div class="grid grid-cols-2 gap-6">

        <div>
            <p class="text-xs uppercase opacity-50">Stock Count</p>
            <p class="text-xl font-black">{{ $data['stock_count'] }}</p>
        </div>

        <div>
            <p class="text-xs uppercase opacity-50">Stock Value</p>
            <p class="text-xl font-black">
                ${{ number_format($data['stock_value'], 2) }}
            </p>
        </div>

        <div>
            <p class="text-xs uppercase opacity-50">Sold Count</p>
            <p class="text-xl font-black">{{ $data['sold_count'] }}</p>
        </div>

        <div>
            <p class="text-xs uppercase opacity-50">Sold Value</p>
            <p class="text-xl font-black">
                ${{ number_format($data['sold_value'], 2) }}
            </p>
        </div>

        <div>
            <p class="text-xs uppercase opacity-50">Favorites</p>
            <p class="text-xl font-black">{{ $data['favorites'] }}</p>
        </div>

        <div>
            <p class="text-xs uppercase opacity-50">Views</p>
            <p class="text-xl font-black">{{ $data['views'] }}</p>
        </div>

    </div>

</div>