<?php

use Livewire\Component;
use App\Models\Product;
use App\Models\ProductColor;

new class extends Component {
    public Product $product;
    public ?int $selectedColorId = null;
    public ?int $selectedVariantId = null;
    public $base_price;

    /*

    |--------------------------------------------------------------------------
    | NATIVE MOUNT LIFECYCLE (AUTOMATIC INITIALIZATION ON PAGE LOAD)
    |--------------------------------------------------------------------------
    */
    public function mount(Product $product)
    {
        $this->product = $product->relationLoaded('colors') 
            ? $product 
            : Product::with('colors.variants')->findOrFail($product->id);

        $this->selectedColorId = $this->product->colors->first()?->id;
        $this->selectedVariantId = null;
        
        $firstColor = $this->product->colors->first();
        if ($firstColor) {
            $firstVariant = $firstColor->variants->first();
            $this->base_price = $firstVariant?->price ?? $this->product->base_price;
        }
    }

    /*

    |--------------------------------------------------------------------------
    | LOAD PRODUCT (ADMIN ENTRY POINT)
    |--------------------------------------------------------------------------
    */
    public function loadProduct($id)
    {
        $this->product = Product::with('colors.variants')->findOrFail($id);
        $this->selectedColorId = $this->product->colors->first()?->id;
        $this->selectedVariantId = null;
    }

    /*

    |--------------------------------------------------------------------------
    | SELECT COLOR
    |--------------------------------------------------------------------------
    */
    public function selectColor($colorId)
    {
        $this->selectedColorId = (int) $colorId;
        $this->selectedVariantId = null;

        $color = $this->product->colors->firstWhere('id', $this->selectedColorId);
        if (! $color) return;

        // OPTIONAL: preload first variant price context
        $firstVariant = $color->variants->first();
        // You can expose this as a derived property OR temporary state
        $this->base_price = $firstVariant?->price ?? $this->product->base_price;
    }

    /*

    |--------------------------------------------------------------------------
    | ACTIVE COLOR
    |--------------------------------------------------------------------------
    */
    public function getActiveColorProperty()
    {
        return $this->product->colors->firstWhere('id', $this->selectedColorId);
    }

    /*

    |--------------------------------------------------------------------------
    | VARIANTS
    |--------------------------------------------------------------------------
    */
    public function getActiveVariantsProperty()
    {
        return $this->activeColor?->variants ?? collect();
    }

    /*

    |--------------------------------------------------------------------------
    | TOGGLE COLOR STATUS (ADMIN CONTROL)
    |--------------------------------------------------------------------------
    */
    public function toggleColor($colorId)
    {
        $color = ProductColor::with('variants')->find($colorId);
        if (! $color) {
            $this->dispatch('notify', message: 'COLOR NOT FOUND', type: 'error');
            return;
        }

        $hasImage = !empty($color->front_image_path);
        $hasVariants = $color->variants->count() > 0;

        // Block going LIVE if incomplete
        if ($color->status === 'draft' && (!$hasImage || !$hasVariants)) {
            $this->dispatch('notify', message: 'LOCKED: IMAGE & SPECS REQUIRED', type: 'error');
            return;
        }

        $color->status = $color->status === 'live' ? 'draft' : 'live';
        $color->save();
        $this->dispatch('$refresh');
    }
}; ?>

<div class="max-w-7xl mx-auto p-4 lg:p-10 lg:grid lg:grid-cols-2 lg:gap-16 font-sans text-black dark:text-white">
    {{-- ========================= FLOATING ADMIN TOOLS ========================== --}}
    <div class="fixed top-24 right-10 z-50 flex flex-col gap-3">
        <flux:button x-on:click="$dispatch('load-visuals-tool', { id: {{ $product->id }} }); $flux.modal('media-modal').show()" icon="photo" circular class="bg-black text-white hover:invert dark:bg-white dark:text-black" />
        <flux:button x-on:click="$dispatch('load-specs-tool', { id: {{ $product->id }} }); $flux.modal('specs-modal').show()" icon="swatch" circular class="bg-black text-white hover:invert dark:bg-white dark:text-black" />
        <flux:button x-on:click="$dispatch('load-editor-tool', { id: {{ $product->id }} }); $flux.modal('edit-modal').show()" icon="pencil-square" circular class="bg-black text-white hover:invert dark:bg-white dark:text-black" />
        <flux:button wire:click="delete" wire:confirm="PERMANENTLY REMOVE FROM MATRIX?" icon="trash" circular variant="danger" />
    </div>

    {{-- ========================= LEFT: VISUALS (ACTIVE COLOR) ========================== --}}
    <div class="space-y-4">
        @php
            $color = $this->activeColor;
            $front = $color?->front_image_path;
            $back = $color?->back_image_path;
        @endphp
        <div class="group relative aspect-[3/4] bg-zinc-100 dark:bg-zinc-900 border border-black dark:border-white overflow-hidden">
            @if($front)
                <img src="{{ asset('storage/' . $front) }}" class="absolute inset-0 w-full h-full object-cover transition-opacity duration-700 group-hover:opacity-0">
            @endif
            @if($back)
                <img src="{{ asset('storage/' . $back) }}" class="absolute inset-0 w-full h-full object-cover opacity-0 transition-opacity duration-700 group-hover:opacity-100">
            @endif
            <div class="absolute bottom-4 left-4 bg-[#E31837] text-white px-3 py-1 text-[10px] font-black uppercase italic">
                {{ $product->gender }}
            </div>
        </div>
    </div>

    {{-- ========================= RIGHT: DETAILS ========================== --}}
    <div class="mt-10 lg:mt-0 space-y-10">
        {{-- HEADER --}}
        <div class="border-b-8 border-black dark:border-white pb-6">
            <h1 class="text-xl md:text-3xl font-black uppercase italic tracking-tighter leading-[0.9] mb-4">
                {{ $product->name }}
            </h1>
            <p class="text-[11px] uppercase tracking-[0.4em] text-[#E31837] font-bold italic">
                {{ $product->category->name ?? 'STUDIO EXCLUSIVE' }}
            </p>
        </div>

        {{-- PRICE (SYNCED WITH COLOR) --}}
        @php
            $variant = $this->activeVariants->first();
            $displayPrice = $variant?->price ?? $product->base_price;
        @endphp
        <div class="flex items-baseline gap-4">
            <span class="text-2xl font-black italic tracking-tighter">
                ${{ number_format((float) $displayPrice, 2) }}
            </span>
        </div>

        {{-- DESCRIPTION --}}
        <div class="italic opacity-90 uppercase tracking-tight text-xs leading-relaxed max-w-md font-medium border-l-2 border-zinc-200 dark:border-zinc-700 pl-4">
            {{ $product->description ?? 'No registry narrative established.' }}
        </div>

        {{-- SPECS --}}
        <div class="grid grid-cols-2 gap-8 py-10 border-y border-black dark:border-white">
            <div>
                <label class="uppercase text-[9px] font-black tracking-widest opacity-40 block mb-1"> Fabrication </label>
                <p class="text-sm uppercase font-bold italic"> {{ $product->material ?? 'N/A' }} </p>
            </div>
            <div>
                <label class="uppercase text-[9px] font-black tracking-widest opacity-40 block mb-1"> Silhouette </label>
                <p class="text-sm uppercase font-bold italic"> {{ $product->fit_type ?? 'N/A' }} </p>
            </div>
        </div>

        {{-- ========================= COLOR MATRIX + INTERACTION ========================== --}}
        <div class="space-y-6">
            <label class="uppercase text-[10px] font-black tracking-widest italic"> Colorway Matrix Control </label>
            
            @foreach($product->colors as $c)
                @php
                    $ready = !empty($c->front_image_path) && $c->variants->count() > 0;
                    $active = $selectedColorId == $c->id;
                @endphp
                <div class="flex items-center justify-between border-b border-black/10 py-3">
                    {{-- CLICKABLE COLOR --}}
                    <div wire:click="selectColor({{ $c->id }})" class="flex items-center gap-4 cursor-pointer">
                        <div class="w-10 h-10 border p-0.5 {{ $active ? 'border-[#E31837] scale-110' : 'border-black dark:border-white opacity-60' }}">
                            <div class="w-full h-full" style="background-color: {{ $c->hex_code }}"></div>
                        </div>
                        <div>
                            <div class="font-bold uppercase text-xs"> {{ $c->color_name }} </div>
                            @if(!$ready)
                                <div class="text-[9px] text-red-500 uppercase">
                                    @if(empty($c->front_image_path)) Image @endif
                                    @if(!$c->variants->count()) Specs @endif Required
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- TOOLS + TOGGLE --}}
                    <div class="flex items-center gap-3">
                 

        <flux:button x-on:click="$dispatch('load-visuals-tool', { id: {{ $product->id }} }); $flux.modal('media-modal').show()" icon="photo" circular class="bg-black text-white hover:invert dark:bg-white dark:text-black text-[10px]" hidden />
        <flux:button x-on:click="$dispatch('load-specs-tool', { id: {{ $product->id }} }); $flux.modal('specs-modal').show()" icon="swatch" circular class="bg-black text-white hover:invert dark:bg-white dark:text-black text-[10px]" hidden/>
                        
                        <div class="flex items-center gap-2 justify-center">
                            <flux:label class="text-[8px] font-black uppercase {{ $c->status === 'draft' ? 'opacity-100' : 'opacity-20' }}"> Draft </flux:label>
                            <flux:switch :checked="$c->status === 'live'" wire:click="toggleColor({{ $c->id }})" class=" {{ $c->status === 'live' ? '[--switch-color:theme(colors.emerald.400)]' : ($ready ? 'opacity-40' : 'opacity-10 pointer-events-none') }}" />
                            <flux:label class="text-[8px] font-black uppercase {{ $c->status === 'live' ? 'text-emerald-500 font-bold opacity-100' : 'opacity-20' }}"> Live </flux:label>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>


</div>
