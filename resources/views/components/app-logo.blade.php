@props([
    'sidebar' => false,
])

@if($sidebar)

<x-app-logo-icon class="h-6 fill-current text-white dark:text-black" />



    <flux:sidebar.brand name="Biggs" {{ $attributes }} hidden>
        <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center rounded-md bg-accent-content text-accent-foreground">
            <x-app-logo-icon class="size-5 fill-current text-white dark:text-black" />
        </x-slot>
    </flux:sidebar.brand>

       {{-- TIECNOC Brand Mark --}}
                <a href="/" class="flex items-center gap-3 group hidden">
                    <div class="flex h-8 w-8 items-center justify-center bg-black text-white dark:bg-white dark:text-black font-black">T</div>
                    <span class="text-lg font-bold uppercase tracking-[0.4em] text-black dark:text-white">Tiecnoc</span>
                </a>
                
@else

<x-app-logo-icon class="h-8 fill-current text-white dark:text-black" />





    <flux:brand name="Biggs" {{ $attributes }} hidden>
        <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center rounded-md bg-accent-content text-accent-foreground">
            <x-app-logo-icon class="size-5 fill-current text-white dark:text-black" />
        </x-slot>
    </flux:brand>
       {{-- TIECNOC Brand Mark --}}
                <a href="/" class="flex items-center gap-3 group hidden">
                    <div class="flex h-8 w-8 items-center justify-center bg-black text-white dark:bg-white dark:text-black font-black">T</div>
                    <span class="text-lg font-bold uppercase tracking-[0.4em] text-black dark:text-white">Tiecnoc</span>
                </a>
@endif
 