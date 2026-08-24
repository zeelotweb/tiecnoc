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

<div class="bg-white text-black min-h-screen">
    <div class="max-w-7xl mx-auto px-4 lg:px-8 py-10 lg:grid lg:grid-cols-2 lg:gap-16">

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
                                class="aspect-[3/4] w-16 sm:w-full bg-zinc-50 border overflow-hidden shrink-0 transition-all
                                {{ $activeImage === $t['key'] ? 'border-black' : 'border-zinc-200 opacity-50 hover:opacity-100' }}">
                            <img src="{{ asset('storage/' . $t['path']) }}" class="w-full h-full object-cover">
                        </button>
                    @endforeach
                </div>
            @endif

            {{-- MAIN STAGE --}}
            <div class="relative flex-1 aspect-[3/4] bg-zinc-50 border border-black overflow-hidden">

                @if($stagePath)
                    <img src="{{ asset('storage/' . $stagePath) }}"
                         wire:key="stage-{{ $selectedColorId }}-{{ $activeImage }}"
                         class="absolute inset-0 w-full h-full object-cover">
                @else
                    <div class="absolute inset-0 flex items-center justify-center text-xs text-zinc-400">
                        No image available
                    </div>
                @endif

                <div class="absolute bottom-4 left-4 bg-black text-white px-3 py-1 text-[10px] font-bold uppercase tracking-widest">
                    {{ $product->gender }}
                </div>
            </div>

        </div>

        {{-- =========================
            RIGHT: DETAILS + DECISION LAYER
        ========================== --}}
        <div class="mt-10 lg:mt-0 space-y-6">

            <div class="border-b border-black pb-6">
                <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#E31837] mb-2">{{ $product->gender }}</p>
                <h1 class="text-2xl md:text-3xl font-black tracking-tight leading-none">{{ $product->name }}</h1>
            </div>

            {{-- PRICE --}}
            @php
                $variant = $this->activeVariants->first();
                $displayPrice = $variant?->price ?? $product->base_price;
                $isOnSale = $product->compare_at_price && $product->compare_at_price > $product->base_price;
            @endphp

            <div class="flex items-baseline gap-3">
                <span class="text-xl font-medium {{ $isOnSale ? 'text-[#E31837]' : '' }}">
                    ${{ number_format((float) $displayPrice, 2) }}
                </span>
                @if($isOnSale)
                    <span class="text-sm text-zinc-400 line-through">
                        ${{ number_format((float) $product->compare_at_price, 2) }}
                    </span>
                @endif
            </div>

            {{-- DESCRIPTION --}}
            @if($product->description)
                <p class="text-sm text-zinc-600 leading-relaxed">
                    {{ $product->description }}
                </p>
            @endif

            {{-- =========================
                COLORS
            ========================== --}}
            <div class="space-y-3 pt-2">
                <p class="text-xs font-medium">
                    Color {{ $color?->color_name ? "— {$color->color_name}" : '' }}
                </p>

                <div class="flex flex-wrap gap-3">
                    @foreach($product->colors as $c)
                        <button wire:click="selectColor({{ $c->id }})"
                                title="{{ $c->color_name }}"
                                class="w-9 h-9 border-2 p-1 transition-all
                                {{ $selectedColorId == $c->id ? 'border-black' : 'border-zinc-200 hover:border-zinc-400' }}">
                            <div class="w-full h-full border border-black/10" style="background-color: {{ $c->hex_code }}"></div>
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- SIZES --}}
            <div class="space-y-3 pt-2">
                <p class="text-xs font-medium">Size</p>

                <div class="flex flex-wrap gap-2">
                    @forelse($this->activeVariants as $v)
                        @php $soldOut = $v->stock_quantity <= 0; @endphp
                        <button
                            wire:click="selectSize({{ $v->id }})"
                            {{ $soldOut ? 'disabled' : '' }}
                            class="w-14 h-11 flex items-center justify-center border text-sm transition-all
                            {{ $soldOut
                                ? 'border-zinc-200 text-zinc-300 cursor-not-allowed'
                                : ($selectedVariantId == $v->id
                                    ? 'bg-black border-black text-white'
                                    : 'border-black hover:bg-black hover:text-white') }}">
                            <span class="{{ $soldOut ? 'line-through' : '' }}">{{ $v->size }}</span>
                        </button>
                    @empty
                        <p class="text-xs text-zinc-500">Out of stock in all sizes.</p>
                    @endforelse
                </div>
            </div>

            {{-- QUANTITY --}}
            @if($selectedVariantId)
                <div class="space-y-3 pt-2">
                    <p class="text-xs font-medium">Quantity</p>

                    <div class="flex items-center border border-black w-fit">
                        <button wire:click="decrementQuantity" type="button"
                                class="w-10 h-10 flex items-center justify-center hover:bg-black hover:text-white transition-all">
                            &minus;
                        </button>
                        <span class="w-12 text-center text-sm">{{ $quantity }}</span>
                        <button wire:click="incrementQuantity" type="button"
                                class="w-10 h-10 flex items-center justify-center hover:bg-black hover:text-white transition-all">
                            &plus;
                        </button>
                    </div>
                </div>
            @endif

            {{-- SPECS --}}
            @if($product->material || $product->fit_type)
                <div class="grid grid-cols-2 gap-6 py-8 border-t border-black">
                    @if($product->material)
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-widest text-zinc-500 mb-1">Material</p>
                            <p class="text-sm">{{ $product->material }}</p>
                        </div>
                    @endif

                    @if($product->fit_type)
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-widest text-zinc-500 mb-1">Fit</p>
                            <p class="text-sm">{{ $product->fit_type }}</p>
                        </div>
                    @endif
                </div>
            @endif

            {{-- CTA --}}
            <div class="pt-4">
                @if($isPaid)
                    <div class="w-full h-14 border border-black flex items-center justify-center text-[11px] font-bold uppercase tracking-[0.2em] text-[#E31837]">
                        Already Purchased
                    </div>
                @else
                    <button
                        wire:click="addToCart"
                        class="w-full h-14 text-[11px] font-bold uppercase tracking-[0.2em] bg-black text-white hover:bg-[#E31837] transition-colors disabled:bg-zinc-200 disabled:text-zinc-400"
                        {{ !$selectedVariantId ? 'disabled' : '' }}>
                        {{ $selectedVariantId ? 'Add to Bag' : 'Select a Size' }}
                    </button>
                @endif
            </div>

        </div>

        {{-- =========================
            RELATED MERCH
        ========================== --}}
        @if($related->count())
            <div class="lg:col-span-2 pt-16 mt-16 border-t border-black space-y-6">

                <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#E31837]">You May Also Like</p>

                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                    @foreach($related as $item)

                        @php
                            $relatedColor = $item->colors->first();
                            $relatedImage = $relatedColor?->front_image_path;
                            $relatedVariant = $relatedColor?->variants->first();
                        @endphp

                        <a href="/merch/{{ $item->slug }}" class="group space-y-2">

                            <div class="aspect-[3/4] bg-zinc-50 border border-black overflow-hidden">
                                @if($relatedImage)
                                    <img src="{{ asset('storage/' . $relatedImage) }}" class="w-full h-full object-cover">
                                @endif
                            </div>

                            <div class="space-y-1">
                                <p class="text-sm">{{ $item->name }}</p>
                                <p class="text-sm font-medium">
                                    ${{ number_format((float) ($relatedVariant?->price ?? $item->base_price), 2) }}
                                </p>
                            </div>

                        </a>

                    @endforeach
                </div>
            </div>
        @endif

    </div>
</div>
