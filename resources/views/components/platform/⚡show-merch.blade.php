<?php

use Livewire\Component;
use App\Models\Product;
use App\Services\MerchService;
use App\Services\Store\CartService;
use App\Services\Store\ActivityService;

new class extends Component {

    public Product $product;

    public $selectedColorId = null;
    public $selectedVariantId = null;

    /*
    |--------------------------------------------------------------------------
    | INIT
    |--------------------------------------------------------------------------
    */
    public function mount(MerchService $service, $slug)
    {
        $this->product = $service->getProductBySlug($slug);

        // only consider live + valid colors (safe against empty or invalid states)
        $this->selectedColorId = $this->product->colors
            ->first()?->id;

        // auto-select first valid variant if available
        $this->selectedVariantId = $this->activeVariants->first()?->id;

    }

    /*
    |--------------------------------------------------------------------------
    | COLOR SELECTION
    |--------------------------------------------------------------------------
    */
    public function selectColor($colorId)
    {
        $this->selectedColorId = $colorId;

        // reset variant safely within new color context
        $this->selectedVariantId = $this->activeVariants->first()?->id;
    }

    /*
    |--------------------------------------------------------------------------
    | SIZE SELECTION
    |--------------------------------------------------------------------------
    */
    public function selectSize($variantId)
    {
        $this->selectedVariantId = $variantId;
    }

    /*
    |--------------------------------------------------------------------------
    | ADD TO CART
    |--------------------------------------------------------------------------
    */
    public function addToCart(CartService $cart, ActivityService $activity)
    {
        if ($activity->owns($this->product)) {
            $this->dispatch('notify', message: 'PIECE ALREADY OWNED', type: 'error');
            return;
        }

        if (!$this->selectedVariantId) {
            $this->dispatch('notify', message: 'SELECT SIZE', type: 'error');
            return;
        }

        try {
            $cart->add($this->selectedVariantId);

            $this->dispatch('notify', message: 'ADDED TO CART', type: 'success');
            $this->dispatch('cart-updated');

            $this->selectedVariantId = null;

        } catch (\Exception $e) {
            $this->dispatch('notify', message: 'SYSTEM ERROR', type: 'error');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | ACTIVE COLOR (LIVE ONLY SAFE RESOLUTION)
    |--------------------------------------------------------------------------
    */
    public function getActiveColorProperty()
    {
        return $this->product->colors
            ->where('status', 'live')
            ->firstWhere('id', $this->selectedColorId);
    }

    /*
    |--------------------------------------------------------------------------
    | ACTIVE VARIANTS (SELLABLE CONTEXT ONLY)
    |--------------------------------------------------------------------------
    */
    public function getActiveVariantsProperty()
    {
        return $this->activeColor?->variants
            ?? collect();
    }

    /*
    |--------------------------------------------------------------------------
    | VIEW DATA
    |--------------------------------------------------------------------------
    */
        public function with(MerchService $service, ActivityService $activity)
        {
            return [
                'isPaid' => $activity->owns($this->product),
                'related' => $service->getRelatedMerch($this->product),
            ];
        }
};

 ?>

<div class="max-w-7xl mx-auto p-4 lg:p-10 lg:grid lg:grid-cols-2 lg:gap-16 font-sans text-black dark:text-white">

    {{-- =========================
        LEFT: VISUAL + IDENTITY LAYER
    ========================== --}}
    <div class="space-y-6">

        @php 
            $color = $this->activeColor;
            $front = $color?->front_image_path;
            $back  = $color?->back_image_path;
        @endphp

        {{-- IMAGE WRAPPER --}}
        <div class="relative group aspect-[3/4] bg-zinc-100 dark:bg-zinc-900 border-2 border-black dark:border-white overflow-hidden cursor-crosshair">

            {{-- NAME OVERLAY --}}
            <div class="absolute top-4 left-4 z-1 bg-black text-white px-3 py-2 opacity-70">
                <h1 class="text-sm md:text-lg font-black uppercase italic tracking-tight">
                    {{ $product->name }}
                </h1>
            </div>

            @if($front)
                <img src="{{ asset('storage/' . $front) }}"
                     class="absolute inset-0 w-full h-full object-cover transition-opacity duration-700 group-hover:opacity-0">
            @endif

            @if($back)
                <img src="{{ asset('storage/' . $back) }}"
                     class="absolute inset-0 w-full h-full object-cover opacity-0 transition-opacity duration-700 group-hover:opacity-100">
            @endif

            <div class="absolute bottom-4 left-4 bg-[#E31837] text-white px-3 py-1 text-[10px] font-black uppercase italic shadow-lg">
                {{ $product->gender }}
            </div>
        </div>

        {{-- =========================
            COLORS (NOW PRIMARY ACTION)
        ========================== --}}
        <div class="space-y-3">

            <label class="uppercase text-[10px] font-black tracking-widest italic">
                Colorway Context
            </label>

            <div class="flex flex-wrap gap-4">
                @foreach($product->colors as $c)
                    <button wire:click="selectColor({{ $c->id }})"
                            class="group transition-transform active:scale-90">

                        <div class="w-8 h-8 border-2 
                            {{ $selectedColorId == $c->id 
                                ? 'border-cyan-600 scale-110 shadow-xl' 
                                : 'border-black dark:border-white opacity-60' }} p-1">

                            <div class="w-full h-full border border-black/10"
                                 style="background-color: {{ $c->hex_code }}">
                            </div>
                        </div>

                    </button>
                @endforeach
            </div>
        </div>

    </div>

    {{-- =========================
        RIGHT: DETAILS + DECISION LAYER
    ========================== --}}
    <div class="mt-10 lg:mt-0 space-y-4">

        {{-- PRICE --}}
        @php
            $variant = $this->activeVariants->first();
            $displayPrice = $variant?->price ?? $product->base_price;
        @endphp

        <div class="flex items-baseline gap-4 border-black dark:border-white {{ $product->description ? 'border-b-1':''}}">
            <span class="text-2xl font-black italic tracking-tighter">
                ${{ number_format((float) $displayPrice, 2) }}
            </span>
        </div>

        {{-- DESCRIPTION --}}
        <div class="italic opacity-90 lowercase tracking-tight text-xs leading-relaxed w-full font-medium border-l-2 border-pink-600 dark:border-cyan-700 p-2 bg-neutral-100 dark:bg-neutral-800 {{ $product->description ? '':'hidden'}}">
            {{ $product->description ?? 'No registry narrative established.' }}
        </div>

        {{-- SIZES (MOVING CLOSER TO CTA FLOW) --}}
        <div class="space-y-4 pt-2">
            <label class="uppercase text-[10px] font-black tracking-widest italic">
                Registered Sizes
            </label>

            <div class="flex flex-wrap gap-3">
                @forelse($this->activeVariants as $v)
                    <button 
                        wire:click="selectSize({{ $v->id }})"
                        class="w-auto h-auto border-1 flex items-center justify-center p-2 transition-all
                        {{ $selectedVariantId == $v->id 
                            ? 'bg-[#E31837] border-[#E31837] text-white' 
                            : 'border-black dark:border-white hover:bg-black hover:text-white dark:hover:bg-white dark:hover:text-black' }}">

                        <span class="text-xs font-black uppercase italic tracking-tighter">
                            {{ $v->size }}
                        </span>
                    </button>
                @empty
                    <p class="text-[10px] opacity-30 italic uppercase font-bold">
                        Size Run Exhausted
                    </p>
                @endforelse
            </div>
        </div>

        {{-- SPECS (PUSHED DOWN) --}}
        <div class="grid grid-cols-2 gap-8 py-10 border-t border-black dark:border-white">
            <div>
                <label class="uppercase text-[9px] font-black tracking-widest block mb-1">
                    Fabrication
                </label>
                <p class="text-sm uppercase font-bold italic">
                    {{ $product->material ?? 'N/A' }}
                </p>
            </div>

            <div class="{{ $product->fit_type ? '':'hidden' }}">
                <label class="uppercase text-[9px] font-black tracking-widest block mb-1">
                    Silhouette
                </label>
                <p class="text-sm uppercase font-bold italic">
                    {{ $product->fit_type ?? 'N/A' }}
                </p>
            </div>
        </div>

        {{-- CTA --}}
        <div class="pt-6">
            @if($isPaid)
                <div class="w-full py-8 border-4 border-black dark:border-white flex flex-col items-center justify-center space-y-1 bg-zinc-50 dark:bg-zinc-900">
                    <span class="uppercase font-black tracking-[0.4em] text-xs italic text-[#E31837]">
                        Transaction Complete
                    </span>
                </div>
            @else
                <button 
                    wire:click="addToCart"
                    class="w-full bg-black text-white dark:bg-white dark:text-black py-8 font-black uppercase italic tracking-widest hover:bg-pink-600"
                    {{ !$selectedVariantId ? 'disabled' : '' }}>

                    {{ $selectedVariantId ? 'Add To Cart' : 'Select Configuration' }}
                </button>
            @endif
        </div>










    </div>

    {{-- =========================
    RELATED MERCH
========================= --}}
@if($related->count())

<div class="pt-16 border-t border-black dark:border-white space-y-6">

    <label class="uppercase text-[10px] font-black tracking-widest italic">
        Related Archive
    </label>

    <div class="grid grid-cols-2 md:grid-cols-3 gap-6">

        @foreach($related as $item)

            @php
                $color = $item->colors->first();
                $image = $color?->front_image_path;
                $variant = $color?->variants->first();
            @endphp

            <a href="/merch/{{ $item->slug }}"
               class="group space-y-2">

                <div class="aspect-[3/4] bg-zinc-100 dark:bg-zinc-900 border border-black dark:border-white overflow-hidden">

                    @if($image)
                        <img src="{{ asset('storage/' . $image) }}"
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    @endif

                    <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition hidden">
                        <flux:icon.eye class="w-4 h-4 text-black dark:text-white" />
                    </div>

                </div>

                <div class="space-y-1">
                    <p class="text-xs font-black uppercase italic tracking-tight">
                        {{ $item->name }}
                    </p>

                    <p class="text-[11px] font-bold text-green-400">
                        ${{ number_format((float) ($variant?->price ?? $item->base_price), 2) }}
                    </p>
                </div>

            </a>

        @endforeach

    </div>
</div>

@endif
</div>
