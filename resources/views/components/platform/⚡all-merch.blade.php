<?php

use Livewire\Component;
use Livewire\WithPagination;
use App\Services\MerchService;

new class extends Component {
    use WithPagination;

    public $gender;
    public $perPage = 10;

    public function mount($gender = 'all')
    {
        $this->gender = $gender;
    }

    public function with(MerchService $service)
    {
        return [
            'discoveryFeed' => $service->getCatalogueByGender($this->perPage, $this->gender),
        ];
    }
};
?>

<div class="min-h-screen bg-white font-sans text-[#00174F]">

    {{-- HEADER --}}
    <nav class="sticky top-0 flex items-center justify-between border-b-2 border-[#00174F] bg-white px-2 py-4 z-5">
        <div class="text-[10px] font-bold uppercase tracking-[0.3em] md:block">
            {{ $gender === 'all' ? 'All Collections' : $gender . ' Collection' }}
        </div>
    </nav>

    {{-- GRID --}}
    <main class="p-2">
        <div class="grid grid-cols-1 gap-px md:grid-cols-5">

            @foreach($discoveryFeed as $product)

                @php
                    $display = app(\App\Services\MerchService::class)->getDisplayData($product);

                    $color   = $display['color'];
                    $variant = $display['variant'];
                    $front   = $display['image']; // string
                    $back    = $display['back'];  // string
                    $price   = $display['price'];
                @endphp

                <div class="group relative flex flex-col bg-white z-1">

                    {{-- IMAGE --}}
                    <div class="relative aspect-[2/3] cursor-pointer overflow-hidden border border-black">

                        <a href="/merch/{{ $product->slug }}">

                            @if($front)
                                <img src="{{ asset('storage/' . $front) }}" 
                                     class="absolute inset-0 h-full w-full object-cover transition-opacity duration-500 {{ $back ? 'group-hover:opacity-0 group-active:opacity-0' : '' }}"
                                     alt="{{ $product->name }}">
                            @endif

                            @if($back)
                                <img src="{{ asset('storage/' . $back) }}" 
                                     class="absolute inset-0 h-full w-full object-cover opacity-0 transition-opacity duration-500 group-hover:opacity-100 group-active:opacity-100"
                                     alt="{{ $product->name }} Detail">
                            @endif

                        </a>

                        {{-- GENDER --}}
                        <div class="absolute left-0 top-0 bg-[#00174F] px-2 py-1 text-[8px] font-black uppercase italic text-white shadow-md">
                            {{ $product->gender }}
                        </div>

                        {{-- REACTION --}}
                        @if($variant)
                            <div class="absolute top-2 right-2 z-5 bg-white/80 backdrop-blur p-1 rounded">

                                <livewire:platform.reaction_button 
                                    :variantId="$variant->id"
                                    type="favorite"
                                    :key="'grid-reaction-'.$variant->id" 
                                />

                            </div>
                        @endif

                    </div>

                    {{-- INFO --}}
                    <div class="p-2">
                        <h2 class="mb-1 text-xs font-black uppercase italic leading-tight tracking-tighter">
                            {{ $product->name }}
                        </h2>

                        <div class="flex items-center justify-between border-t border-[#00174F] pt-1">
                            <span class="text-sm font-black">
                                ${{ number_format((float) $price, 2) }}
                            </span>

                            <a href="/merch/{{ $product->slug }}" 
                               class="border-b-2 border-[#E31837] text-[9px] font-black uppercase italic transition-colors hover:text-[#E31837]">
                                View Full
                            </a>
                        </div>
                    </div>

                </div>

            @endforeach

        </div>

        <div class="mt-4">
            {{-- pagination later --}}
        </div>

    </main>

</div>