<?php

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Product;

new class extends Component {
    use WithPagination;

    public $perPage = 20;

    // TRUE = show products with at least one LIVE color
    // FALSE = show products with NO live colors
    public bool $viewLive = true;

    protected $listeners = ['matrix-updated' => '$refresh', 'product-created' => '$refresh'];

    /*
    |--------------------------------------------------------------------------
    | LOAD MORE
    |--------------------------------------------------------------------------
    */
    public function loadMore()
    {
        $this->perPage += 20;
    }

    /*
    |--------------------------------------------------------------------------
    | DATA SOURCE (ALIGNED WITH ProductColor.status)
    |--------------------------------------------------------------------------
    */
    public function with()
    {
        $query = Product::with(['category', 'colors']);

        if ($this->viewLive) {
            // Products that have at least ONE live color
            $query->whereHas('colors', function ($q) {
                $q->where('status', 'live');
            });
        } else {
            // Products with ZERO live colors
            $query->whereDoesntHave('colors', function ($q) {
                $q->where('status', 'live');
            });
        }

        return [
            'products' => $query
                ->latest()
                ->paginate($this->perPage),
        ];
    }

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
};
?>
<div class="space-y-6">
    {{-- HEADER --}}
    <div class="flex flex-wrap items-center justify-between gap-4">
        <p class="text-sm text-zinc-500">
            {{ $products->count() }} {{ $products->count() === 1 ? 'product' : 'products' }} · {{ $viewLive ? 'on the floor' : 'in the studio' }}
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

    {{-- TABLE --}}
    <div class=" border border-black/15 dark:border-white/15 overflow-hidden">
    <flux:table>
        <flux:table.columns>
            <flux:table.column>Product</flux:table.column>
            <flux:table.column>Category</flux:table.column>
            <flux:table.column>Price</flux:table.column>
            <flux:table.column>Status</flux:table.column>
            <flux:table.column class="text-right">Actions</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse($products as $product)
                @php $isLive = $product->colors->contains(fn ($c) => $c->status === 'live'); @endphp
                <flux:table.row :key="$product->id">
                    {{-- NAME --}}
                    <flux:table.cell class="font-medium">
                        {{ $product->name }}
                    </flux:table.cell>

                    {{-- CATEGORY --}}
                    <flux:table.cell class="text-zinc-500">
                        {{ $product->category->name ?? '—' }}
                    </flux:table.cell>

                    {{-- PRICE --}}
                    <flux:table.cell>
                        ${{ number_format($product->base_price, 2) }}
                    </flux:table.cell>

                    {{-- STATUS --}}
                    <flux:table.cell>
                        <flux:badge :color="$isLive ? 'emerald' : 'zinc'" size="sm">
                            {{ $isLive ? 'Live' : 'Draft' }}
                        </flux:badge>
                    </flux:table.cell>

                    {{-- TOOLS --}}
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
                    <flux:table.cell colspan="5" class="text-center py-12 text-zinc-500">
                        Nothing in the studio yet.
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>
    </div>

    {{-- LOAD MORE --}}
    @if($products->hasMorePages())
        <div class="flex justify-center pt-4">
            <flux:button wire:click="loadMore" variant="ghost">
                Load More
            </flux:button>
        </div>
    @endif
</div>
