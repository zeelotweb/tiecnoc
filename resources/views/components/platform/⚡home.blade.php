<?php

use Livewire\Component;
use App\Services\MerchService;

new class extends Component {

    public function with(MerchService $service)
    {
        return [
            'men'    => $service->getFeaturedByGender('male'),
            'women'  => $service->getFeaturedByGender('female'),
            'unisex' => $service->getFeaturedByGender('unisex'),
            'sale'   => $service->getCatalogueByGender(4, 'all', true),
            'arrivals' => $service->getCatalogueByGender(8, 'all', false),
        ];
    }
};
?>

<div class="bg-white text-black min-h-screen">

    {{-- ============ HERO ============ --}}
    <section class="border-b border-black">
        <div class="max-w-7xl mx-auto px-4 lg:px-8 py-20 lg:py-32">
            <p class="text-[10px] font-bold uppercase tracking-[0.3em] text-[#E31837] mb-4">The Studio Line</p>
            <h1 class="text-5xl md:text-7xl font-black tracking-tight leading-[0.95] max-w-3xl">
                Built for the floor, worn on the street.
            </h1>
            <p class="text-sm md:text-base text-zinc-500 max-w-lg mt-6">
                Tiecnoc is a working wardrobe — real fabrics, real fit, made for people who move. New pieces land often. Nothing sits precious.
            </p>
            <div class="flex flex-wrap gap-3 mt-8">
                <a href="{{ route('store.all') }}" wire:navigate
                   class="h-12 px-6 inline-flex items-center text-[11px] font-bold uppercase tracking-[0.2em] bg-black text-white hover:bg-[#E31837] transition-colors">
                    Shop All
                </a>
                <a href="{{ route('store.sale') }}" wire:navigate
                   class="h-12 px-6 inline-flex items-center text-[11px] font-bold uppercase tracking-[0.2em] border border-black hover:bg-black hover:text-white transition-colors">
                    Shop Sale
                </a>
            </div>
        </div>
    </section>

    {{-- ============ CATEGORY TILES ============ --}}
    <section class="max-w-7xl mx-auto px-4 lg:px-8 py-16">
        <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#E31837] mb-6">Shop By Category</p>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @foreach([
                ['label' => 'Men', 'route' => 'store.male', 'product' => $men],
                ['label' => 'Women', 'route' => 'store.female', 'product' => $women],
                ['label' => 'Unisex', 'route' => 'store.unisex', 'product' => $unisex],
            ] as $tile)
                @php
                    $image = $tile['product']?->colors->first()?->front_image_path;
                @endphp
                <a href="{{ route($tile['route']) }}" wire:navigate class="group relative aspect-[4/5] bg-zinc-50 border border-black overflow-hidden block">
                    @if($image)
                        <img src="{{ asset('storage/' . $image) }}" class="absolute inset-0 w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                    @endif
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>
                    <div class="absolute bottom-0 left-0 right-0 p-5 flex items-center justify-between">
                        <span class="text-white text-lg font-black tracking-tight">{{ $tile['label'] }}</span>
                        <span class="text-white text-[10px] font-bold uppercase tracking-[0.2em] border-b border-white pb-0.5">Shop</span>
                    </div>
                </a>
            @endforeach
        </div>
    </section>

    {{-- ============ SALE STRIP ============ --}}
    @if($sale->count())
        <section class="bg-black text-white">
            <div class="max-w-7xl mx-auto px-4 lg:px-8 py-14">
                <div class="flex items-end justify-between mb-8">
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-[0.3em] text-[#E31837] mb-2">Limited Time</p>
                        <h2 class="text-2xl md:text-3xl font-black tracking-tight">On Sale Now</h2>
                    </div>
                    <a href="{{ route('store.sale') }}" wire:navigate class="text-[10px] font-bold uppercase tracking-[0.2em] border-b border-white hover:text-[#E31837] hover:border-[#E31837] transition-colors">
                        View All
                    </a>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    @foreach($sale as $product)
                        @php
                            $display = app(MerchService::class)->getDisplayData($product);
                            $front = $display['image'];
                            $price = $display['price'];
                            $compareAtPrice = $display['compareAtPrice'];
                        @endphp
                        <a href="/merch/{{ $product->slug }}" class="group space-y-2">
                            <div class="aspect-[3/4] bg-zinc-900 border border-white/20 overflow-hidden">
                                @if($front)
                                    <img src="{{ asset('storage/' . $front) }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                                @endif
                            </div>
                            <p class="text-sm truncate">{{ $product->name }}</p>
                            <div class="flex items-center gap-2">
                                <span class="text-sm text-[#E31837] font-medium">${{ number_format((float) $price, 2) }}</span>
                                <span class="text-xs text-zinc-500 line-through">${{ number_format((float) $compareAtPrice, 2) }}</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ============ NEW ARRIVALS ============ --}}
    @if($arrivals->count())
        <section class="max-w-7xl mx-auto px-4 lg:px-8 py-16">
            <div class="flex items-end justify-between mb-8 border-b border-black pb-6">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#E31837] mb-2">Fresh In</p>
                    <h2 class="text-2xl md:text-3xl font-black tracking-tight">New Arrivals</h2>
                </div>
                <a href="{{ route('store.all') }}" wire:navigate class="text-[10px] font-bold uppercase tracking-[0.2em] border-b border-black hover:text-[#E31837] hover:border-[#E31837] transition-colors">
                    View All
                </a>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-x-4 gap-y-10">
                @foreach($arrivals as $product)
                    @php
                        $display = app(MerchService::class)->getDisplayData($product);
                        $variant = $display['variant'];
                        $front   = $display['image'];
                        $back    = $display['back'];
                        $price   = $display['price'];
                        $compareAtPrice = $display['compareAtPrice'];
                        $isOnSale = $compareAtPrice && $compareAtPrice > $price;
                    @endphp

                    <div class="group">
                        <div class="relative aspect-[3/4] bg-zinc-50 border border-black overflow-hidden">
                            <a href="/merch/{{ $product->slug }}">
                                @if($front)
                                    <img src="{{ asset('storage/' . $front) }}"
                                         class="absolute inset-0 w-full h-full object-cover transition-opacity duration-500 {{ $back ? 'group-hover:opacity-0' : '' }}"
                                         alt="{{ $product->name }}">
                                @endif
                                @if($back)
                                    <img src="{{ asset('storage/' . $back) }}"
                                         class="absolute inset-0 w-full h-full object-cover opacity-0 transition-opacity duration-500 group-hover:opacity-100"
                                         alt="{{ $product->name }} detail">
                                @endif
                            </a>

                            @if($isOnSale)
                                <div class="absolute top-0 left-0 bg-[#E31837] text-white px-2 py-1 text-[9px] font-bold uppercase tracking-widest">Sale</div>
                            @endif

                            @if($variant)
                                <div class="absolute top-2 right-2 bg-white/80 backdrop-blur p-1">
                                    <livewire:platform.reaction_button :variantId="$variant->id" type="favorite" :key="'home-reaction-'.$variant->id" />
                                </div>
                            @endif
                        </div>

                        <div class="pt-2">
                            <p class="text-sm font-medium truncate">{{ $product->name }}</p>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="text-sm {{ $isOnSale ? 'text-[#E31837] font-medium' : '' }}">${{ number_format((float) $price, 2) }}</span>
                                @if($isOnSale)
                                    <span class="text-sm text-zinc-400 line-through">${{ number_format((float) $compareAtPrice, 2) }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

</div>
