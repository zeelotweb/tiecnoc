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
    public $activeImage = 'front'; // 'front' | 'back' — which color asset the main stage shows
    public $quantity = 1;

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

        // auto-select first IN-STOCK variant if available — never pre-select
        // a sold-out size, or "Add to Cart" would go live on it.
        $this->selectedVariantId = $this->firstInStockVariant()?->id;

    }

    protected function firstInStockVariant()
    {
        return $this->activeVariants->first(fn ($v) => $v->stock_quantity > 0);
    }

    /*
    |--------------------------------------------------------------------------
    | COLOR SELECTION
    |--------------------------------------------------------------------------
    */
    public function selectColor($colorId)
    {
        $this->selectedColorId = $colorId;
        $this->activeImage = 'front';

        // reset variant safely within new color context
        $this->selectedVariantId = $this->firstInStockVariant()?->id;
    }

    /*
    |--------------------------------------------------------------------------
    | THUMBNAIL SELECTION (MAIN STAGE)
    |--------------------------------------------------------------------------
    */
    public function selectImage($which)
    {
        $this->activeImage = $which;
    }

    /*
    |--------------------------------------------------------------------------
    | SIZE SELECTION
    |--------------------------------------------------------------------------
    */
    public function selectSize($variantId)
    {
        $variant = $this->activeVariants->firstWhere('id', $variantId);

        if (!$variant || $variant->stock_quantity <= 0) return;

        $this->selectedVariantId = $variantId;
    }

    /*
    |--------------------------------------------------------------------------
    | QUANTITY
    |--------------------------------------------------------------------------
    */
    public function incrementQuantity()
    {
        $max = $this->activeVariants->firstWhere('id', $this->selectedVariantId)?->stock_quantity ?? 99;
        $this->quantity = min($this->quantity + 1, max($max, 1));
    }

    public function decrementQuantity()
    {
        $this->quantity = max(1, $this->quantity - 1);
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
            $cart->add($this->selectedVariantId, $this->quantity);

            $this->dispatch('notify', message: 'ADDED TO CART', type: 'success');
            $this->dispatch('cart-updated');

            $this->selectedVariantId = null;
            $this->quantity = 1;

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
        LEFT: GALLERY (THUMBNAIL RAIL + MAIN STAGE)
    ========================== --}}
    <div class="flex flex-col-reverse sm:flex-row gap-4">

        @php
            $color = $this->activeColor;
            $front = $color?->front_image_path;
            $back  = $color?->back_image_path;
            $thumbs = collect([
                $front ? ['key' => 'front', 'path' => $front] : null,
                $back  ? ['key' => 'back',  'path' => $back]  : null,
            ])->filter()->values();
            $stagePath = $activeImage === 'back' && $back ? $back : $front;
        @endphp

        {{-- THUMBNAIL RAIL --}}
        @if($thumbs->count() > 1)
            <div class="flex sm:flex-col gap-3 sm:w-20 shrink-0">
                @foreach($thumbs as $t)
                    <button wire:click="selectImage('{{ $t['key'] }}')"
                            class="aspect-[3/4] w-16 sm:w-full bg-zinc-100 dark:bg-zinc-900 border-2 overflow-hidden shrink-0 transition-all
                            {{ $activeImage === $t['key'] ? 'border-black dark:border-white' : 'border-transparent opacity-50 hover:opacity-100' }}">
                        <img src="{{ asset('storage/' . $t['path']) }}" class="w-full h-full object-cover">
                    </button>
                @endforeach
            </div>
        @endif

        {{-- MAIN STAGE --}}
        <div class="relative flex-1 aspect-[3/4] bg-zinc-100 dark:bg-zinc-900 border-2 border-black dark:border-white overflow-hidden">

            {{-- NAME OVERLAY --}}
            <div class="absolute top-4 left-4 z-10 bg-black text-white px-3 py-2 opacity-70">
                <h1 class="text-sm md:text-lg font-black uppercase italic tracking-tight">
                    {{ $product->name }}
                </h1>
            </div>

            @if($stagePath)
                <img src="{{ asset('storage/' . $stagePath) }}"
                     wire:key="stage-{{ $selectedColorId }}-{{ $activeImage }}"
                     class="absolute inset-0 w-full h-full object-cover">
            @else
                <div class="absolute inset-0 flex items-center justify-center text-[10px] uppercase tracking-widest opacity-30 italic">
                    No Visual Registered
                </div>
            @endif

            <div class="absolute bottom-4 left-4 bg-[#E31837] text-white px-3 py-1 text-[10px] font-black uppercase italic shadow-lg">
                {{ $product->gender }}
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

        {{-- =========================
            COLORS
        ========================== --}}
        <div class="space-y-3 pt-2">
            <label class="uppercase text-[10px] font-black tracking-widest italic">
                Colorway {{ $color?->color_name ? "/ {$color->color_name}" : '' }}
            </label>

            <div class="flex flex-wrap gap-4">
                @foreach($product->colors as $c)
                    <button wire:click="selectColor({{ $c->id }})"
                            title="{{ $c->color_name }}"
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
                    @php $soldOut = $v->stock_quantity <= 0; @endphp
                    <button
                        wire:click="selectSize({{ $v->id }})"
                        {{ $soldOut ? 'disabled' : '' }}
                        class="relative w-auto h-auto border-1 flex items-center justify-center p-2 transition-all
                        {{ $soldOut
                            ? 'border-black/20 dark:border-white/20 opacity-30 cursor-not-allowed'
                            : ($selectedVariantId == $v->id
                                ? 'bg-[#E31837] border-[#E31837] text-white'
                                : 'border-black dark:border-white hover:bg-black hover:text-white dark:hover:bg-white dark:hover:text-black') }}">

                        <span class="text-xs font-black uppercase italic tracking-tighter {{ $soldOut ? 'line-through' : '' }}">
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

        {{-- QUANTITY --}}
        @if($selectedVariantId)
            <div class="space-y-4 pt-2">
                <label class="uppercase text-[10px] font-black tracking-widest italic">
                    Quantity
                </label>

                <div class="flex items-center border-2 border-black dark:border-white w-fit">
                    <button wire:click="decrementQuantity" type="button"
                            class="w-10 h-10 flex items-center justify-center font-black text-lg hover:bg-black hover:text-white dark:hover:bg-white dark:hover:text-black transition-all">
                        &minus;
                    </button>
                    <span class="w-12 text-center font-black italic">{{ $quantity }}</span>
                    <button wire:click="incrementQuantity" type="button"
                            class="w-10 h-10 flex items-center justify-center font-black text-lg hover:bg-black hover:text-white dark:hover:bg-white dark:hover:text-black transition-all">
                        &plus;
                    </button>
                </div>
            </div>
        @endif

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
