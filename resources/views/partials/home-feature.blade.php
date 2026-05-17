@php
    $color = $product?->colors->first();
    $variant = $color?->variants->first();
    $front = $color?->front_image_path;
    $back  = $color?->back_image_path;
@endphp

@if($product)  
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 border border-black dark:border-white">

    {{-- IMAGE SIDE --}}
    <div class="relative group aspect-[3/4] lg:aspect-auto overflow-hidden bg-zinc-100 dark:bg-zinc-900">

        <a href="{{ route('merchandise.show', $product->slug ?? '#') }}">
            {{-- FRONT --}}
            @if($front)
                <img src="{{ asset('storage/' . $front) }}"
                     class="absolute inset-0 w-full h-full object-cover transition-opacity duration-500 group-hover:opacity-0">
            @endif

            {{-- BACK --}}
            @if($back)
                <img src="{{ asset('storage/' . $back) }}"
                     class="absolute inset-0 w-full h-full object-cover opacity-0 transition-opacity duration-500 group-hover:opacity-100">
            @endif
        </a>

    </div>

    {{-- INFO SIDE --}}
    <div class="p-6 lg:p-10 space-y-6 flex flex-col justify-center bg-black dark:bg-neutral-50 text-white dark:text-black">

        {{-- Header Row: Name and Favorite Button --}}
        <div class="flex justify-between items-start gap-4">
            <h1 class="text-xl lg:text-3xl font-black uppercase italic tracking-tighter">
                {{ $product->name ?? ''}}
            </h1>

            <div class="flex-shrink-0">
                <livewire:platform.reaction_button 
                    :variantId="$product->id"
                    type="favorite"
                    :key="'home-feature-fav-'.$product->id" 
                />
            </div>
        </div>

        <p class="text-xs uppercase tracking-widest opacity-50">
            {{ $product->description ?? ''}}
        </p>

        {{-- PRICE (variant fallback to base) --}}
        <div class="text-lg font-black italic">
            ${{ number_format($variant?->price ?? $product->base_price ?? 0, 2) }}
        </div>

        {{-- COLORS --}}
        <div class="space-y-2">
            <p class="text-[10px] uppercase tracking-[0.3em] opacity-40">Colors</p>
            <div class="flex gap-2 p-1">
                @foreach($product->colors as $c)
                    <div class="w-4 h-4 border border-white"
                         style="background-color: {{ $c->hex_code }}">
                    </div>
                @endforeach
            </div>
        </div>

        {{-- SIZES --}}
        <div class="space-y-2">
            <p class="text-[10px] uppercase tracking-[0.3em] opacity-40">Sizes</p>
            <div class="flex flex-wrap gap-2 text-xs font-black">
                @foreach($product->colors as $c)
                    @foreach($c->variants as $v)
                        <span class="border px-2 py-1">
                            {{ $v->size }}
                        </span>
                    @endforeach
                @endforeach
            </div>
        </div>

        {{-- CTA --}}
<div class="pt-4 flex items-center justify-between w-full px-2">
    {{-- LEFT: Action Link --}}
    <a href="{{ route('merchandise.show', $product->slug ?? '#') }}" 
       class="inline-block border border-white dark:border-black px-6 py-3 text-[10px] uppercase font-black tracking-widest hover:bg-pink-600 hover:text-white transition-all">
        View Piece →
    </a>

    {{-- RIGHT: Cart Trigger --}}


<div class="flex-shrink-0 text-black dark:hover:bg-neutral-800 bg-neutral-50 dark:bg-neutral-500">
    <flux:button 
        icon="shopping-cart" 
        circular 
        variant="filled" 
        wire:key="cart-{{ $product->id }}"
        @click="
            window.dispatchEvent(new CustomEvent('quick-add', { 
                detail: [{{ $product->id }}] 
            }));
            $flux.modal('quick-add-modal').show();
        "
        class="hover:bg-black hover:text-pink-600 transition-all" 
    />
</div>

    {{-- GLOBAL MODAL --}}


</div>

    </div>

</div>
@endif

