{{-- hero-x-card.blade.php --}}
<div x-data="{ active: 0, slides: {{ count($slides) }} }" class="relative h-[80vh] w-full overflow-hidden bg-white dark:bg-black hidden">
    
    {{-- Animated Logo Intro Overlay --}}
    <div x-show="active === 0" x-init="setTimeout(() => active = 1, 3000)" 
         class="absolute inset-0 z-50 flex items-center justify-center bg-black text-white">
        <div class="animate-pulse tracking-[1.5em] text-4xl font-bold uppercase">
            <x-app-logo-icon class="scale-150" />
        </div>
    </div>

    @foreach($slides as $index => $slide)
    <div x-show="active === {{ $index + 1 }}" 
         class="flex h-full flex-col md:flex-row items-center transition-all duration-1000">
        
        {{-- Visual Side --}}
        <div class="h-1/2 w-full md:h-full md:w-1/2 overflow-hidden">
            <img src="{{ $slide->image_path }}" class="h-full w-full object-cover grayscale hover:grayscale-0 transition-all duration-700">
        </div>

        {{-- Content Side --}}
        <div class="flex h-1/2 w-full md:h-full md:w-1/2 flex-col justify-center px-12 md:px-24">
            <span class="text-[0.6rem] uppercase tracking-[0.6em] text-zinc-500 mb-4">{{ $slide->label }}</span>
            <h1 class="text-4xl md:text-6xl font-light uppercase tracking-tighter leading-none mb-8">
                {{ $slide->title }}
            </h1>
            <a href="/shop/{{ $slide->slug }}" class="w-fit border-b border-black py-2 text-xs font-bold uppercase tracking-[0.4em] dark:border-white transition-all hover:pl-4">
                Shop Collection
            </a>
        </div>
    </div>
    @endforeach

    {{-- Progress Indicators (Tommy Style) --}}
    <div class="absolute bottom-10 right-10 flex gap-4">
        @foreach($slides as $i => $s)
            <button @click="active = {{ $i + 1 }}" 
                    :class="active === {{ $i + 1 }} ? 'w-12 bg-black dark:bg-white' : 'w-4 bg-zinc-300'" 
                    class="h-1 transition-all duration-500"></button>
        @endforeach
    </div>
</div>
