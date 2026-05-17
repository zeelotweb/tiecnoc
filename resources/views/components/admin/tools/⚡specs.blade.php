<?php

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\Product;
use App\Services\Admin\ProductContextService;

new class extends Component {

    public $product_id;

    /*
    |--------------------------------------------------------------------------
    | ACTIVE COLOR CONTEXT
    |--------------------------------------------------------------------------
    */
    public $active_color_id = null;
    public $active_color = '';
    public $active_hex = '';

    /*
    |--------------------------------------------------------------------------
    | SPEC FIELDS
    |--------------------------------------------------------------------------
    */
    public $size = '';
    public $price = '';
    public $stock = 0;

    public $base_price = 0;

    /*
    |--------------------------------------------------------------------------
    | INIT
    |--------------------------------------------------------------------------
    */
    #[On('load-specs-tool')]
    public function load($id)
    {   
        $this->product_id = $id;

        $this->reset([
            'active_color_id',
            'active_color',
            'active_hex',
            'size',
            'price',
            'stock',
        ]);

        $product = Product::find($id);
        $this->base_price = $product?->base_price ?? 0;
    }

    /*
    |--------------------------------------------------------------------------
    | SELECT COLOR
    |--------------------------------------------------------------------------
    */
public function selectActiveColor($colorId, ProductContextService $service)
{
    $color = $service->getColor(
        (int) $this->product_id,
        (int) $colorId
    );

    if (! $color) return;

    $this->active_color_id = $color->id;
    $this->active_color    = $color->color_name;
    $this->active_hex      = $color->hex_code;

    $variant = $service->getColorVariants($color->id)->first();

    $this->price = $variant?->price ?? $this->base_price;
    $this->stock = $variant?->stock_quantity ?? 0;
    $this->size  = $variant?->size ?? '';
}



    /*
    |--------------------------------------------------------------------------
    | SAVE / UPSERT SPEC
    |--------------------------------------------------------------------------
    */
    public function saveSpec(ProductContextService $service)
    {
        $this->validate([
            'active_color_id' => 'required|exists:product_colors,id',
            'size'            => 'required|string',
            'price'           => 'nullable|numeric',
            'stock'           => 'nullable|numeric',
        ]);

        $service->upsertVariant(
            $this->active_color_id,
            $this->size,
            $this->price,
            $this->stock ?? 0
        );

        $this->dispatch('notify', message: 'SPEC UPDATED');
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE VARIANT
    |--------------------------------------------------------------------------
    */
    public function removeVariant($id)
    {
        \App\Models\ProductVariant::where('id', $id)
            ->whereHas('color', function ($q) {
                $q->where('product_id', $this->product_id);
            })
            ->delete();

        $this->dispatch('notify', message: 'VARIANT REMOVED');
    }

    /*
    |--------------------------------------------------------------------------
    | COLORS (FOR BLADES $this->colors)
    |--------------------------------------------------------------------------
    */
    public function getColorsProperty(ProductContextService $service)
    {
        if (! $this->product_id) {
            return collect();
        }

        return $service->getProductColors($this->product_id);
    }

    /*
    |--------------------------------------------------------------------------
    | VARIANTS (FOR BLADES $this->variants)
    |--------------------------------------------------------------------------
    */
    public function getVariantsProperty(ProductContextService $service)
    {
        if (! $this->active_color_id) {
            return collect();
        }

        return $service->getColorVariants($this->active_color_id);
    }
};
?>

<div class="w-full overflow-hidden p-6 space-y-12 bg-white dark:bg-black text-black dark:text-white border border-black/10">

    {{-- HEADER --}}
    <header class="flex flex-col border-b-2 border-black dark:border-white pb-8 gap-6">

        <h1 class="text-3xl font-black italic uppercase tracking-tighter leading-none">
            SPEC MATRIX BUILDER
        </h1>

        <div class="flex flex-col gap-2">
            <p class="text-[10px] uppercase tracking-[0.4em] opacity-40 italic">
                {{ $active_color ? "CONTEXT: $active_color" : 'AWAITING_SELECTION' }}
            </p>

            @if($active_hex)
                <div class="w-4 h-4 border-2 border-black dark:border-white"
                     style="background-color: {{ $active_hex }}"></div>
            @endif
        </div>

    </header>

    {{-- COLORS (TOP SELECTOR - ALWAYS VISIBLE) --}}
    <div class="flex flex-col gap-4">

        <label class="uppercase text-[11px] font-black tracking-[0.2em]">
            Available Colors
        </label>

        <select wire:change="selectActiveColor($event.target.value)"
                class="border p-3 uppercase font-black">

            <option value="">Select Color</option>

            @foreach($this->colors as $c)
                <option value="{{ $c->id }}">
                    {{ $c->color_name }}
                </option>
            @endforeach

        </select>

    </div>

    {{-- FORM --}}
    <div class="{{ !$active_color_id ? 'opacity-30 pointer-events-none' : '' }} flex flex-col gap-4">

        <input wire:model="size" placeholder="Size" class="border p-2 uppercase font-black">
        <input wire:model="price" placeholder="Price" class="border p-2 font-black">
        <input wire:model="stock" type="number" placeholder="Stock" class="border p-2 font-black">

        <button wire:click="saveSpec"
                class="bg-black text-white uppercase text-[10px] font-black p-3">
            Save Specs
        </button>

    </div>

    {{-- CURRENT VARIANT --}}
    <div class="flex flex-col gap-2">

        @foreach($this->variants as $v)
            <div class="text-[10px] uppercase font-black border p-2">
                {{ $v->size }} | ${{ $v->price ?? 'BASE' }} | {{ $v->stock_quantity }}
            </div>
        @endforeach

    </div>

    {{-- DELETE COLORS --}}
    <div class="border-t pt-6 flex flex-col gap-2">

        @foreach($this->colors as $c)
            <div class="flex justify-between text-[10px] uppercase">

                <span>{{ $c->color_name }}</span>

                <button wire:click="deleteColor({{ $c->id }})"
                        class="text-red-500">
                    Delete
                </button>

            </div>
        @endforeach

    </div>

</div>