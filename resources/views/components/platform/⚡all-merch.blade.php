<?php

use Livewire\Component;
use App\Services\MerchService;
use Illuminate\Support\Str;

new class extends Component {

    public $gender = 'all';
    public $onSale = false;
    public $perPage = 16;

    public function mount($gender = 'all', $onSale = false)
    {
        $this->gender = $gender;
        $this->onSale = $onSale;
    }

    public function loadMore()
    {
        $this->perPage += 16;
    }

    public function with(MerchService $service)
    {
        return [
            'discoveryFeed' => $service->getCatalogueByGender($this->perPage, $this->gender, $this->onSale),
        ];
    }

    public function getHeadingProperty(): string
    {
        if ($this->onSale) return 'Sale';

        return match ($this->gender) {
            'male' => 'Men',
            'female' => 'Women',
            'unisex' => 'Unisex',
            default => 'All',
        };
    }
};
?>

<div class="bg-white text-black min-h-screen">

    {{-- HEADER --}}
    <div class="max-w-7xl mx-auto px-4 lg:px-8 pt-10 pb-6 border-b border-black">
        <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#E31837] mb-2">Catalogue</p>
        <h1 class="text-3xl md:text-4xl font-black tracking-tight leading-none">{{ $this->heading }}</h1>
        <p class="text-sm text-zinc-500 mt-3">{{ $discoveryFeed->total() }} {{ Str::plural('piece', $discoveryFeed->total()) }}</p>
    </div>

    {{-- GRID --}}
    <div class="max-w-7xl mx-auto px-2 lg:px-8 py-8">
        @if($discoveryFeed->count())
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-x-4 gap-y-10">
                @foreach($discoveryFeed as $product)
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
                        {{-- IMAGE --}}
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
                                <div class="absolute top-0 left-0 bg-[#E31837] text-white px-2 py-1 text-[9px] font-bold uppercase tracking-widest">
                                    Sale
                                </div>
                            @endif

                            @if($variant)
                                <div class="absolute top-2 right-2 z-10 bg-white/80 backdrop-blur p-1">
                                    <livewire:platform.reaction_button
                                        :variantId="$variant->id"
                                        type="favorite"
                                        :key="'grid-reaction-'.$variant->id"
                                    />
                                </div>
                            @endif
                        </div>

                        {{-- INFO --}}
                        <div class="pt-2">
                            <p class="text-sm font-medium truncate">{{ $product->name }}</p>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="text-sm {{ $isOnSale ? 'text-[#E31837] font-medium' : '' }}">
                                    ${{ number_format((float) $price, 2) }}
                                </span>
                                @if($isOnSale)
                                    <span class="text-sm text-zinc-400 line-through">${{ number_format((float) $compareAtPrice, 2) }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- INFINITE SCROLL --}}
            @if($discoveryFeed->hasMorePages())
                <div x-intersect="$wire.loadMore()" class="flex items-center justify-center pt-14 pb-4">
                    <div wire:loading wire:target="loadMore" class="text-[10px] font-bold uppercase tracking-[0.2em] text-zinc-400">
                        Loading more&hellip;
                    </div>
                </div>
            @endif
        @else
            <div class="text-center py-24 text-sm text-zinc-500 border border-dashed border-zinc-300">
                Nothing here yet.
            </div>
        @endif
    </div>

</div>
