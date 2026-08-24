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
    // Direct-embed entry point (product edit page tab).
    public function mount($productId = null)
    {
        if ($productId) {
            $this->load($productId);
        }
    }

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
    | REFRESH (colorways changed in another tool tab — Livewire tabs don't
    | share state, so this just needs to exist to trigger a re-render and
    | pull $this->colors fresh)
    |--------------------------------------------------------------------------
    */
    #[On('colorway-updated')]
    public function refreshColors()
    {
        //
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
    | DELETE COLOR
    |--------------------------------------------------------------------------
    */
    public function deleteColor($id)
    {
        $color = \App\Models\ProductColor::where('id', $id)
            ->where('product_id', $this->product_id)
            ->first();

        if (! $color) return;

        if ($this->active_color_id === $color->id) {
            $this->reset(['active_color_id', 'active_color', 'active_hex', 'size', 'price', 'stock']);
        }

        $color->delete();
        $this->dispatch('notify', message: 'COLOR REMOVED');
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

<div class="space-y-8">

    {{-- COLORS (TOP SELECTOR - ALWAYS VISIBLE) --}}
    <div class="flex flex-col gap-3">
        <div class="flex items-center gap-3">
            <flux:label>Colorway</flux:label>
            @if($active_hex)
                <div class="w-4 h-4 rounded border border-zinc-300 dark:border-zinc-700"
                     style="background-color: {{ $active_hex }}"></div>
            @endif
        </div>

        <flux:select wire:change="selectActiveColor($event.target.value)">
            <flux:select.option value="">Select a colorway</flux:select.option>
            @foreach($this->colors as $c)
                <flux:select.option value="{{ $c->id }}">{{ $c->color_name }}</flux:select.option>
            @endforeach
        </flux:select>
    </div>

    {{-- FORM --}}
    <div class="{{ !$active_color_id ? 'opacity-40 pointer-events-none' : '' }} grid grid-cols-3 gap-4 items-end">
        <flux:input wire:model="size" label="Size" placeholder="M" />
        <flux:input wire:model="price" label="Price" placeholder="0.00" icon="currency-dollar" />
        <flux:input wire:model="stock" type="number" label="Stock" placeholder="0" />

        <flux:button wire:click="saveSpec" variant="primary" class="col-span-3">
            Save Size
        </flux:button>
    </div>

    {{-- CURRENT VARIANTS --}}
    @if($this->variants->count())
        <div class=" border border-black/15 dark:border-white/15 divide-y divide-black/15 dark:divide-white/15">
            @foreach($this->variants as $v)
                <div class="flex items-center justify-between px-4 py-2.5 text-sm">
                    <span class="font-medium">{{ $v->size }}</span>
                    <span class="text-zinc-500">${{ $v->price ?? 'base' }} · {{ $v->stock_quantity }} in stock</span>
                </div>
            @endforeach
        </div>
    @endif

    {{-- COLORWAYS --}}
    @if($this->colors->count())
        <div class="border-t border-black/15 dark:border-white/15 pt-6 space-y-2">
            <flux:label>All Colorways</flux:label>
            @foreach($this->colors as $c)
                <div class="flex items-center justify-between text-sm py-1">
                    <span>{{ $c->color_name }}</span>
                    <flux:button wire:click="deleteColor({{ $c->id }})" wire:confirm="Delete this colorway and its sizes?" variant="ghost" size="sm">
                        Delete
                    </flux:button>
                </div>
            @endforeach
        </div>
    @endif

</div>