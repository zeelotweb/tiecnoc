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
        $query = Product::query();

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

    {{-- Matrix Table --}}
    <div class=" border border-black/15 dark:border-white/15 overflow-hidden">
    <flux:table>
        <flux:table.columns class="w-full">
            <flux:table.column>Product</flux:table.column>
            <flux:table.column>Price</flux:table.column>
            <flux:table.column>Status</flux:table.column>
            <flux:table.column class="text-right">Actions</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse($products as $product)
                @php $isLive = $product->colors->contains(fn ($c) => $c->status === 'live'); @endphp
                <flux:table.row :key="$product->id" class="group">
                    <flux:table.cell class="font-medium">{{ $product->name }}</flux:table.cell>
                    <flux:table.cell class="text-zinc-500">${{ number_format($product->base_price, 2) }}</flux:table.cell>

                    <flux:table.cell>
                        <flux:badge :color="$isLive ? 'emerald' : 'zinc'" size="sm">
                            {{ $isLive ? 'Live' : 'Draft' }}
                        </flux:badge>
                    </flux:table.cell>

@php
    $user = auth()->user();
    $isAdmin = $user->isSuperAdmin() || $user->isAdmin();
@endphp

<flux:table.cell class="text-right">
    <flux:dropdown>
        <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />

        <flux:menu>

            <flux:menu.item icon="pencil-square"
                x-on:click="
                    $dispatch('load-editor-tool', { id: {{ $product->id }} });
                    $dispatch('load-visuals-tool', { id: {{ $product->id }} });
                    $dispatch('load-specs-tool', { id: {{ $product->id }} });
                    $dispatch('load-metrics-tool', { id: {{ $product->id }} });
                    $dispatch('media-gallery-modal', { id: {{ $product->id }} });
                    $dispatch('load-media-tool', { id: {{ $product->id }} });
                    $flux.modal('product-edit-modal').show();
                ">
                Edit
            </flux:menu.item>

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
</flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="4" class="text-center py-10 text-zinc-500"> Nothing in the studio yet </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>
    </div>
</section>
