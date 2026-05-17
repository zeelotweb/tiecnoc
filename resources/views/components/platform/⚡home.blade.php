<?php

use Livewire\Component;
use Livewire\WithPagination;
use App\Services\MerchService;

new class extends Component {
    use WithPagination;

    public $perPage = 10;

    public function with(MerchService $service)
    {
        // 1. Featured Archives (aligned via service rules)
        $men    = $service->getFeaturedByGender('male');
        $women  = $service->getFeaturedByGender('female');
        $unisex = $service->getFeaturedByGender('unisex');

        // 2. Gallery (single source of truth from service)
        $gallery = $service->getGalleryContext();

        return [
            'discoveryFeed' => $service->getCatalogueByGender($this->perPage),

            'men'    => $men,
            'women'  => $women,
            'unisex' => $unisex,

            'gallery' => $gallery,
        ];
    }
};

?>

<div class="bg-transparent[">

    {{-- ================= HERO SECTIONS ================= --}}
    <div class="space-y-14 max-w-7xl mx-auto p-2 lg:p-4">

        {{-- GALLERY SECTION --}}
        @if($gallery['product'])
            <section class="space-y-6">
                <h2 class="text-[10px] uppercase tracking-[0.5em] font-black">GALLERY</h2>
                 @include('partials.home-galery', [
                    'product' => $gallery['product'], 
                    'promo'   => $gallery['promo']
                ])
            </section>
        @endif

        {{-- GENDER ARCHIVES --}}
        <section class="space-y-6">
            <h2 class="text-[10px] uppercase tracking-[0.5em] font-black">MEN / SELECTED</h2>
            @include('partials.home-feature', ['product' => $men])
        </section>

        <section class="space-y-6">
            <h2 class="text-[10px] uppercase tracking-[0.5em] font-black">WOMEN / SELECTED</h2>
            @include('partials.home-feature', ['product' => $women])
        </section>

        <section class="space-y-6">
            <h2 class="text-[10px] uppercase tracking-[0.5em] font-black">UNISEX / SELECTED</h2>
            @include('partials.home-feature', ['product' => $unisex])
        </section>

    </div>

    {{-- ================= DISCOVERY GRID ================= --}}
    <div class="min-h-screen bg-white font-sans text-[#00174F] mt-12">
        <div class="mt-6 text-[10px] font-bold uppercase tracking-[0.3em] px-4 mb-4">
            New Draft Collections
        </div>





<main class="p-2">
    <div class="grid grid-cols-2 md:grid-cols-5 gap-px bg-gray-200 border border-gray-200">
        @foreach($discoveryFeed as $item)
            @php
                $display = app(MerchService::class)->getDisplayData($item);
                $front   = $display['image'];
                $back    = $display['back'];
                $price   = $display['price'];
            @endphp

            <div class="group relative flex flex-col bg-white">
                
                {{-- MEDIA --}}
                <div class="aspect-[2/3] relative overflow-hidden bg-gray-50 cursor-pointer">
                    <a href="/merch/{{ $item->slug }}">
                        @if($front)
                            <img src="{{ asset('storage/' . $front) }}"
                                 class="absolute inset-0 w-full h-full object-cover transition-opacity duration-500 {{ $back ? 'group-hover:opacity-0' : '' }}">
                        @endif

                        @if($back)
                            <img src="{{ asset('storage/' . $back) }}"
                                 class="absolute inset-0 w-full h-full object-cover opacity-0 transition-opacity duration-500 group-hover:opacity-100">
                        @endif
                    </a>

                    {{-- GENDER TAG --}}
                    <div class="absolute top-0 left-0 bg-[#00174F] text-white text-[8px] font-black uppercase px-2 py-1 italic">
                        {{ $item->gender }}
                    </div>
                </div>

                {{-- PRODUCT INFO --}}
                <div class="p-6">
                    {{-- Horizontal row for Name and Fav Button --}}
                    <div class="flex justify-between items-start mb-4">
                        <h2 class="text-xs font-black uppercase italic tracking-tighter leading-tight">
                            {{ $item->name }}
                        </h2>

                        {{-- Fav button at the end right using item id --}}
                        <div class="flex-shrink-0 ml-2">
                            <livewire:platform.reaction_button 
                                :variantId="$item->id"
                                :type="'favorite'"
                                :key="'fav-grid-'.$item->id" 
                            />
                        </div>
                    </div>

                    <div class="flex justify-between items-center border-t-2 border-[#00174F] pt-4">
                        <span class="font-black text-sm">
                            ${{ number_format((float) $price, 2) }}
                        </span>

                        <a href="/merch/{{ $item->slug }}" class="text-[9px] font-black uppercase italic border-b-2 border-[#E31837]">
                            View Full         
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</main>


    </div>



{{-- layouts/app.blade.php --}}
<flux:modal name="quick-add-modal" class="md:w-96">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">
            
            </flux:heading>
             <flux:heading size="xl" class="flex uppercase tracking-tighter font-black italic break-words leading-none items-center mt-4">
                {{ 'Add to Cart' }}
            </flux:heading>
        </div>

        <livewire:store.quick-add-view />

        <div class="flex gap-2">
            <flux:spacer />
            <flux:modal.close>
                <flux:button variant="ghost">Cancel</flux:button>
            </flux:modal.close>
        </div>
    </div>
</flux:modal>

</div>

