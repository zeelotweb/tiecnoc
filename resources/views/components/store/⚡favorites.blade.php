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

<div class="bg-white text-black min-h-screen">
    <div class="max-w-7xl mx-auto px-4 lg:px-8 py-10 space-y-10">

        <div class="border-b border-black pb-6">
            <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#E31837] mb-2">Saved</p>
            <h1 class="text-3xl md:text-4xl font-black tracking-tight leading-none">Favorites</h1>
        </div>

        @if($items->isEmpty())
            <div class="py-24 text-center text-sm text-zinc-500 border border-dashed border-zinc-300">
                No favorites yet.
            </div>
        @else

            <div class="divide-y divide-black/10">

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

                    <div class="flex gap-6 py-8 first:pt-0 group">

                        {{-- IMAGE --}}
                        <div class="w-28 aspect-[3/4] bg-zinc-50 border border-black overflow-hidden shrink-0">
                            @if($front)
                                <img src="{{ asset('storage/' . $front) }}" class="w-full h-full object-cover">
                            @endif
                        </div>

                        {{-- INFO --}}
                        <div class="flex-1 space-y-3">

                            <div class="flex justify-between items-start">
                                <p class="font-medium text-sm">{{ $product->name ?? 'Unknown Product' }}</p>
                                <flux:button
                                    variant="ghost"
                                    icon="x-mark"
                                    wire:click="remove({{ $item->id }})"
                                    size="xs"
                                />
                            </div>

                            {{-- VARIANT DETAILS --}}
                            @if($variant)
                                <div class="flex items-center gap-2 text-xs text-zinc-500">
                                    <span>{{ $variant->size }}</span>
                                    @if($color)
                                        <span class="text-zinc-300">/</span>
                                        <div class="flex items-center gap-1.5">
                                            <div class="w-3 h-3 border border-black" style="background-color: {{ $color->hex_code }}"></div>
                                            <span>{{ $color->color_name }}</span>
                                        </div>
                                    @endif
                                </div>
                            @endif

                            <div class="pt-4">
                                <span class="text-sm font-medium">${{ number_format((float) $price, 2) }}</span>
                            </div>

                        </div>
                    </div>

                @endforeach

            </div>

        @endif

    </div>
</div>