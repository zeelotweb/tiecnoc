<?php

use Livewire\Component;
use App\Models\Product;

new class extends Component {
    // TRUE = show products with at least one LIVE color
    // FALSE = show products with NO live colors
    public bool $viewLive = true; 

    protected $listeners = [
        'product-created' => '$refresh', 
        'matrix-updated' => '$refresh'
    ];

    /*
    |--------------------------------------------------------------------------
    | SOFT DELETE
    |--------------------------------------------------------------------------
    */
    public function softDelete($productId)
    {
        $product = Product::findOrFail($productId);
        $product->delete();

        $this->dispatch('matrix-updated');
    }

    /*
    |--------------------------------------------------------------------------
    | DATA SOURCE (COLOR-DRIVEN)
    |--------------------------------------------------------------------------
    */
    public function with(): array
    {
        $query = Product::with('colors');

        if ($this->viewLive) {
            // Products with at least one LIVE color
            $query->whereHas('colors', fn ($q) => $q->where('status', 'live'));
        } else {
            // Products with NO LIVE colors
            $query->whereDoesntHave('colors', fn ($q) => $q->where('status', 'live'));
        }

        return [
            'products' => $query
                ->latest()
                ->take(10)
                ->get(),
        ];
    }
};
?>

<section class="space-y-4">
    {{-- Header with Toggle --}}
    <div class="flex items-center justify-between gap-4">
        <p class="text-sm text-zinc-500">
            {{ $viewLive ? 'On the floor' : 'In the studio' }}
        </p>

        <div class="flex items-center gap-3">
            <flux:label class="text-sm {{ !$viewLive ? 'text-zinc-900 dark:text-white font-medium' : 'text-zinc-400' }}">Studio</flux:label>
            <flux:switch
                wire:model.live="viewLive"
                variant="inline"
                class="{{ $viewLive ? '[--switch-color:theme(colors.emerald.500)]' : '' }}"
            />
            <flux:label class="text-sm {{ $viewLive ? 'text-emerald-600 dark:text-emerald-400 font-medium' : 'text-zinc-400' }}">Floor</flux:label>
        </div>
    </div>

    {{-- GRID --}}
    @php $isAdmin = auth()->user()->isSuperAdmin() || auth()->user()->isAdmin(); @endphp

    @if($products->count())
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-x-4 gap-y-6">
            @foreach($products as $product)
                @php
                    $isLive = $product->colors->contains(fn ($c) => $c->status === 'live');
                    $color = $product->colors->firstWhere('status', 'live') ?? $product->colors->first();
                    $image = $color?->front_image_path;
                @endphp

                <div class="group cursor-pointer" x-on:click="
                        $dispatch('load-editor-tool', { id: {{ $product->id }} });
                        $dispatch('load-visuals-tool', { id: {{ $product->id }} });
                        $dispatch('load-specs-tool', { id: {{ $product->id }} });
                        $dispatch('load-metrics-tool', { id: {{ $product->id }} });
                        $dispatch('media-gallery-modal', { id: {{ $product->id }} });
                        $dispatch('load-media-tool', { id: {{ $product->id }} });
                        $flux.modal('product-edit-modal').show();
                    ">

                    <div class="aspect-[3/4] bg-zinc-100 dark:bg-zinc-900 border border-black/15 dark:border-white/15 overflow-hidden mb-2">
                        @if($image)
                            <img src="{{ asset('storage/' . $image) }}" class="w-full h-full object-cover group-hover:opacity-90 transition-opacity">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-[10px] text-zinc-400 uppercase tracking-widest">No Image</div>
                        @endif
                    </div>

                    <p class="text-sm font-medium truncate">{{ $product->name }}</p>

                    <div class="flex items-center justify-between mt-1">
                        <span class="text-sm text-zinc-600 dark:text-zinc-400">${{ number_format($product->base_price, 2) }}</span>

                        <flux:dropdown x-on:click.stop>
                            <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                            <flux:menu>
                                <flux:menu.item icon="eye"
                                    href="{{ route('merchandise.show', $product->slug ?? $product->id) }}"
                                    target="_blank">
                                    View Live
                                </flux:menu.item>

                                @if($isAdmin)
                                    <flux:menu.separator />
                                    <flux:menu.item icon="trash"
                                        variant="danger"
                                        wire:click="softDelete({{ $product->id }})"
                                        wire:confirm="Move this product to trash?">
                                        Delete
                                    </flux:menu.item>
                                @endif
                            </flux:menu>
                        </flux:dropdown>
                    </div>

                    <div class="flex justify-end mt-1">
                        <flux:badge :color="$isLive ? 'emerald' : 'zinc'" size="sm">
                            {{ $isLive ? 'Live' : 'Draft' }}
                        </flux:badge>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-10 text-sm text-zinc-500 border border-dashed border-black/15 dark:border-white/15">
            Nothing in the studio yet.
        </div>
    @endif
</section>
