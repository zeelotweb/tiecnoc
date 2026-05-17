@php
    $mediaType = $promo?->type ?? ''; 
    $promoUrl = $promo ? asset('storage/' . $promo->path) : null;
    
    // Core Image Assets
    $firstColor = $product->colors->first();
    $frontImage = $firstColor?->front_image_path ? asset('storage/' . $firstColor->front_image_path) : null;
    $backImage = $firstColor?->back_image_path ? asset('storage/' . $firstColor->back_image_path) : null;
    
    // Fallback logic
    $displayMedia = $promoUrl ?? $frontImage;

    // Flags
    $isVideo = str_contains($mediaType, 'video');
    $isAudio = str_contains($mediaType, 'audio');
    $isPDF   = str_contains($mediaType, 'pdf');

    // Get color previews
    $previewColors = $product->colors->take(2);
@endphp

<section class="relative w-full h-[85vh] min-h-[750px] overflow-hidden bg-black font-sans group">
    
    {{-- LAYER 1: THE GHOST BACKGROUND (Immersive) --}}
    <div class="absolute inset-0 z-0">
        <img src="{{ $frontImage }}" class="w-full h-full object-cover opacity-20 blur-3xl scale-110">
        <div class="absolute inset-0 bg-gradient-to-b from-black via-transparent to-black"></div>
    </div>

    {{-- LAYER 2: THE ASYMMETRIC STAGE --}}
    <div class="absolute inset-0 z-1 flex items-center justify-end pr-0 md:pr-[10%] pointer-events-none">
        <div class="w-full md:w-2/3 h-full md:h-[100%] relative overflow-hidden shadow-[0_0_80px_rgba(0,0,0,0.8)] border-y md:border border-white/5 pointer-events-auto">
            
            @if($isVideo)
                <video autoplay muted loop playsinline class="w-full h-full object-cover">
                    <source src="{{ $promoUrl }}" type="{{ $mediaType }}">
                </video>
            @else
                <div class="relative w-full h-full">
                    <img src="{{ $displayMedia }}" 
                         class="absolute inset-0 w-full h-full object-cover transition-opacity duration-1000 ease-in-out {{ $backImage ? 'group-hover:opacity-0' : '' }}">
                    
                    @if($backImage)
                        <img src="{{ $backImage }}" 
                             class="absolute inset-0 w-full h-full object-cover opacity-0 group-hover:opacity-100 transition-opacity duration-1000 ease-in-out">
                    @endif
                </div>
            @endif

            {{-- Grain Texture --}}
            <div class="absolute inset-0 opacity-[0.05] pointer-events-none bg-[url('https://vercel.app')]"></div>
        </div>
    </div>

    {{-- LAYER 3: VERTICAL TYPOGRAPHY (The Brand Statement) --}}
    <div class="absolute top-0 left-0 h-full w-20 md:w-40 z-2 flex items-center justify-center border-r border-white/10 hidden md:flex">
        <h1 class="rotate-180 [writing-mode:vertical-lr] text-[8vh] font-black uppercase tracking-tighter text-white/5 select-none transition-all duration-700 group-hover:text-white/20">
            {{ $product->name }}
        </h1>
    </div>

    {{-- LAYER 4: THE FULL-WIDTH CONTROL BAR (Strategic UI) --}}
    <div class="absolute inset-x-0 bottom-0 z-3 pointer-events-none">
        
        {{-- THE TINTED SCRIM BAR --}}
        <div class="relative w-full bg-black/60  py-8 px-6 md:px-20 pointer-events-auto ">
            
            <div class="max-w-screen-2xl mx-auto flex flex-col md:flex-row items-end md:items-center justify-between gap-10">
                
                {{-- Product Identity --}}
                <div class="w-full md:w-1/2">
                    <div class="flex items-center gap-4 mb-2">
                        <span class="bg-white text-black text-[9px] font-black px-2 py-0.5 uppercase tracking-widest">
                            New Release
                        </span>
                        


                        <span class="text-white/40 text-[9px] font-bold uppercase tracking-[0.4em]">
                            Ref.00{{ $product->id }}
                        </span>
                    </div>
                    <h2 class="text-5xl md:text-8xl font-black text-white uppercase tracking-tighter leading-[0.8] transition-transform duration-700">
                        {{ $product->name }}
                    </h2>
                </div>

                {{-- The "Archive" Metadata Section --}}
                <div class="flex flex-wrap items-center gap-12 border-l border-white/10 pl-10">
                    
                    {{-- Color Preview Cards --}}
                    <div class="flex items-center gap-3">
                        <span class="[writing-mode:vertical-lr] rotate-180 text-[8px] font-bold uppercase text-white/30 tracking-widest">Tones</span>
                        <div class="flex -space-x-3">
                            @foreach($previewColors as $color)
                                <div class="w-14 h-18 border border-white/20 bg-zinc-900 overflow-hidden shadow-xl hover:translate-y-[-5px] transition-transform">
                                    <img src="{{ asset('storage/' . $color->front_image_path) }}" class="w-full h-full object-cover">
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Dynamic Actions (Audio/PDF) --}}
                    <div class="flex flex-col gap-4 min-w-[150px]">
                        @if($isAudio)
                            <button onclick="let a = document.getElementById('a-{{ $product->id }}'); a.paused ? a.play() : a.pause()" 
                                    class="text-white flex items-center gap-3 text-[10px] font-black uppercase tracking-widest group/audio">
                                <div class="w-8 h-8 rounded-full border border-white/20 flex items-center justify-center group-hover/audio:bg-white group-hover/audio:text-black transition-all">
                                    <svg class="w-2.5 h-2.5 fill-current" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                </div>
                                <audio id="a-{{ $product->id }}" src="{{ $promoUrl }}"></audio>
                                Play Sound
                            </button>
                        @endif
                        
                        @if($isPDF)
                            <a href="{{ $promoUrl }}" target="_blank" class="text-white/40 hover:text-white transition-colors text-[10px] font-black uppercase tracking-widest flex items-center gap-2">
                                <span class="w-1.5 h-1.5 bg-red-600 rounded-full"></span>
                                Visual Archive
                            </a>
                        @endif

                        <div class="text-3xl font-mono text-white tracking-tighter italic">
                            ${{ number_format($product->base_price, 2) }}
                        </div>
                    </div>

                    {{-- Main Action --}}
                    <a href="/merch/{{ $product->slug }}" class="relative group/btn bg-white text-black px-12 py-5 font-black uppercase text-xs tracking-[0.3em] overflow-hidden">
                        <span class="relative z-10">
                        Acquire Piece
                        </span>
                    <div class="absolute inset-0 bg-zinc-300 translate-y-full transition-transform duration-500 group-hover/btn:translate-y-0"></div>
                    </a>


                                            {{-- Fav Button next to badge --}}
                        <div class="flex-shrink-0">
                            <livewire:platform.reaction_button 
                                :variantId="$product->id"
                                :type="'favorite'"
                                :key="'fav-gallery-'.$product->id" 
                            />
                        </div>
                </div>

            </div>
        </div>
    </div>
</section>
