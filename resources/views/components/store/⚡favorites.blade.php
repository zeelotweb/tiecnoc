<?php

use Livewire\Component;
use App\Services\Store\ReactionService;
use App\Models\Reaction;

new class extends Component {

    /*
    |--------------------------------------------------------------------------
    | REMOVE FAVORITE
    |--------------------------------------------------------------------------
    */
    public function remove($id)
    {
        try {
            Reaction::findOrFail($id)->delete();

            $this->dispatch('notify', message: 'REMOVED', type: 'error');
        } catch (\Exception $e) {
            $this->dispatch('notify', message: 'ERROR', type: 'error');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | DATA SOURCE
    |--------------------------------------------------------------------------
    */
    public function with(ReactionService $service)
    {
        return [
            'items' => $service->getByType('favorite'),
        ];
    }
};
?>

<div class="p-8 lg:p-24 max-w-7xl mx-auto space-y-16 animate-in fade-in duration-700">

    {{-- HEADER --}}
    <header class="border-b border-black dark:border-white pb-8">
        <flux:heading size="xl" class="uppercase font-black tracking-tighter italic">
            Your Favorites
        </flux:heading>

        <p class="text-[9px] uppercase tracking-[0.5em] opacity-40 mt-2 font-black">
            Saved Selections
        </p>
    </header>

    @if($items->isEmpty())
        <div class="py-40 text-center opacity-20 uppercase tracking-[0.6em] text-[10px] font-black italic">
            No favorites yet.
        </div>
    @else

        <div class="space-y-12">

            @foreach($items as $item)

                @php
                    $variant = $item->variant ?? null;
                    $product = $variant?->product;

                    $color = $variant?->color ?? null;

                    $front = $color?->front_image_path;

                    $price = ($variant && $variant->price > 0)
                        ? $variant->price
                        : ($product->base_price ?? 0);
                @endphp

                <div class="flex gap-8 border-b border-zinc-100 dark:border-zinc-900 pb-12 group">

                    {{-- IMAGE --}}
                    <div class="w-28 aspect-[3/4] bg-zinc-50 dark:bg-zinc-950 border border-black overflow-hidden">

                        @if($front)
                            <img src="{{ asset('storage/' . $front) }}"
                                 class="w-full h-full object-cover grayscale group-hover:grayscale-0 transition-all duration-500">
                        @endif

                    </div>

                    {{-- INFO --}}
                    <div class="flex-1 space-y-2">

                        <div class="flex justify-between items-start">

                            <h4 class="uppercase font-black text-sm tracking-tighter italic">
                                {{ $product->name ?? 'Unknown Product' }}
                            </h4>

                            <x-ui.glass class="opacity-0 group-hover:opacity-100 transition">
                                <flux:button
                                    icon="x-mark"
                                    wire:click="remove({{ $item->id }})"
                                    size="xs"
                                />
                            </x-ui.glass>

                        </div>

                        {{-- VARIANT DETAILS --}}
                        <div class="flex items-center gap-3 text-[9px] uppercase tracking-widest font-black opacity-40 italic">

                            @if($variant)
                                <span>{{ $variant->size }}</span>

                                <span class="opacity-30">/</span>

                                @if($color)
                                    <div class="flex items-center gap-1.5">

                                        <div class="w-3 h-3 border border-black dark:border-white"
                                             style="background-color: {{ $color->hex_code }}">
                                        </div>

                                        <span>{{ $color->color_name }}</span>

                                    </div>
                                @endif
                            @endif

                        </div>

                        {{-- PRICE --}}
                        <div class="pt-6 flex justify-between items-end">

                            <span class="text-[10px] uppercase font-black tracking-widest">
                                Saved Item
                            </span>

                            <span class="font-mono text-sm font-black italic">
                                ${{ number_format((float) $price, 2) }}
                            </span>

                        </div>

                    </div>
                </div>

            @endforeach

        </div>

    @endif

</div>