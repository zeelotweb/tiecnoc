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

    protected $listeners = ['matrix-updated' => '$refresh'];

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
<div class="p-6 space-y-10">
    {{-- HEADER --}}
    <flux:header class="flex-col w-full justify-start items-start">
        <flux:heading size="xl" class="uppercase italic font-black tracking-tighter">
            Full Catalog Matrix
        </flux:heading>
        <flux:subheading class="uppercase text-[10px] tracking-[0.4em]">
            Complete Registry Archive
        </flux:subheading>

        {{-- 🔥 GLOBAL TOGGLE --}}
    <div class="flex items-center gap-3 mt-6 justify-end w-full">
        <flux:label class="text-[10px] uppercase font-bold tracking-widest {{ !$viewLive ? 'opacity-100' : 'opacity-30' }}"> 
            Drafts 
        </flux:label>
    
        <flux:switch 
            wire:model.live="viewLive" 
            variant="inline"
            class="{{ $viewLive ? '[--switch-color:theme(colors.emerald.400)] opacity-100' : 'opacity-40' }}"
        />
        
        <flux:label class="text-[10px] uppercase font-bold tracking-widest {{ $viewLive ? 'text-emerald-500 font-bold opacity-100' : 'opacity-30' }}"> 
            Live 
        </flux:label>
    </div>
        
        <p class="text-[10px] uppercase tracking-[0.4em] italic mt-2 text-right w-full {{ $viewLive ? 'text-emerald-500 font-bold' : 'opacity-50' }}">
            Viewing: {{ $viewLive ? 'Live Storefront' : 'Staging (Drafts)' }}
        </p>
    </flux:header>

    {{-- TABLE --}}
    <flux:table>
        <flux:table.columns>
            <flux:table.column class="uppercase text-[9px] tracking-widest font-black">Identity</flux:table.column>
            <flux:table.column class="uppercase text-[9px] tracking-widest font-black">Category</flux:table.column>
            <flux:table.column class="uppercase text-[9px] tracking-widest font-black">Price</flux:table.column>
            <flux:table.column class="hidden uppercase text-[9px] tracking-[0.4em] font-black">Status</flux:table.column>
            <flux:table.column class="hidden uppercase text-[9px] tracking-widest font-black text-right">Tools</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @foreach($products as $product)
                <flux:table.row :key="$product->id">
                    {{-- NAME --}}
                    <flux:table.cell class="font-bold uppercase italic text-sm">
                        {{ $product->name }}
                    </flux:table.cell>

                    {{-- CATEGORY --}}
                    <flux:table.cell class="text-[10px] uppercase opacity-60">
                        {{ $product->category->name ?? '—' }}
                    </flux:table.cell>

                    {{-- PRICE --}}
                    <flux:table.cell class="font-mono text-xs">
                        ${{ number_format($product->base_price, 2) }}
                    </flux:table.cell>

                    {{-- 🔥 STATUS TOGGLE --}}
                    <flux:table.cell>
<div class="hidden flex items-center gap-3 mt-6 justify-end w-full">
    <flux:label class="text-[10px] uppercase font-bold tracking-widest {{ !$viewLive ? 'opacity-100' : 'opacity-30' }}"> 
        Drafts 
    </flux:label>
    
    <flux:switch 
        wire:model.live="viewLive" 
        variant="inline"
        class="{{ $viewLive ? '[--switch-color:theme(colors.emerald.400)] opacity-100' : 'opacity-40' }}"
    />
    
    <flux:label class="text-[10px] uppercase font-bold tracking-widest {{ $viewLive ? 'text-emerald-500 font-bold opacity-100' : 'opacity-30' }}"> 
        Live 
    </flux:label>
</div>
                    </flux:table.cell>

                    {{-- TOOLS --}}
@php
    $user = auth()->user();

    $isAdmin = $user->isSuperAdmin() || $user->isAdmin();
    $isStaff = $user->isStaff();

    $tools = $user->tools?->pluck('tool')->toArray() ?? [];

    $hasAnyTool = $isAdmin || count($tools) > 0;

    // helper
    $can = fn ($tool) => $isAdmin || in_array($tool, $tools);
@endphp

<flux:table.cell class="text-right">
    <flux:dropdown>

        {{-- TRIGGER --}}
        <flux:button 
            variant="ghost" 
            size="sm" 
            icon="ellipsis-horizontal"
            class="hover:bg-black hover:text-white transition-all rounded-none
                   {{ !$hasAnyTool ? 'opacity-30 cursor-not-allowed pointer-events-none' : '' }}"
        />

        <flux:menu class="min-w-[220px] rounded-none border-black border-2 shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] dark:border-white">

            {{-- ADD VISUALS --}}
            @if($can('media'))
                <flux:menu.item icon="photo"
                    class="uppercase text-[10px] font-black tracking-widest"
                    x-on:click="$dispatch('load-visuals-tool', { id: {{ $product->id }} }); $flux.modal('media-modal').show();">
                    Add Visuals
                </flux:menu.item>
            @endif

            {{-- MANAGE SPECS --}}
            @if($can('specs'))
                <flux:menu.item icon="swatch"
                    class="uppercase text-[10px] font-black tracking-widest"
                    x-on:click="$dispatch('load-specs-tool', { id: {{ $product->id }} }); $flux.modal('specs-modal').show();">
                    Manage Specs
                </flux:menu.item>
            @endif

            {{-- EDIT --}}
            @if($can('editor'))
                <flux:menu.item icon="pencil-square"
                    class="uppercase text-[10px] font-black tracking-widest"
                    x-on:click="$dispatch('load-editor-tool', { id: {{ $product->id }} }); $flux.modal('edit-modal').show();">
                    Edit Info
                </flux:menu.item>
            @endif

            @if($isAdmin || $can('metrics') || $can('gallery'))
                <flux:menu.separator class="bg-black/10 dark:bg-white/10" />
            @endif

            {{-- METRICS --}}
            @if($can('metrics'))
                <flux:menu.item icon="chart-bar"
                    class="uppercase text-[10px] font-black tracking-widest"
                    x-on:click="$dispatch('load-metrics-tool', { id: {{ $product->id }} }); $flux.modal('metrics-modal').show();">
                    Metrics
                </flux:menu.item>
            @endif

            {{-- ADD MEDIA --}}
            @if($can('gallery'))
                <flux:menu.item icon="photo"
                    class="uppercase text-[10px] font-black tracking-widest"
                    x-on:click="$dispatch('media-gallery-modal', { id: {{ $product->id }} }); setTimeout(() => { window.initProductGalleryPond(); }, 100); $flux.modal('media-gallery-modal').show();">
                    Add Media
                </flux:menu.item>
            @endif

            {{-- ALWAYS ALLOWED (READ ONLY) --}}
            <flux:menu.item icon="photo"
                class="uppercase text-[10px] font-black tracking-widest"
                x-on:click="$dispatch('load-media-tool', { id: {{ $product->id }} }); $flux.modal('load-media-tool').show();">
                View Media
            </flux:menu.item>

            <flux:menu.separator class="bg-black/10 dark:bg-white/10" />

            <flux:menu.item icon="eye"
                class="group uppercase text-[10px] font-black tracking-[0.2em] py-3"
                href="{{ route('admin.merchandise.show', $product->id) }}">
                View Page
                <flux:spacer />
                <span class="opacity-0 group-hover:opacity-100 transition-opacity italic text-[8px]">VIEW</span>
            </flux:menu.item>

            {{-- DELETE (ADMIN ONLY) --}}
            @if($isAdmin)
                <flux:menu.item icon="trash"
                    variant="danger"
                    class="group uppercase text-[10px] font-black tracking-[0.2em] py-3"
                    wire:click="softDelete({{ $product->id }})"
                    wire:confirm="MOVE TO TRASH / ARCHIVE?">
                    Delete Merch
                    <flux:spacer />
                    <span class="opacity-0 group-hover:opacity-100 transition-opacity italic text-[8px]">DEL</span>
                </flux:menu.item>
            @endif

        </flux:menu>
    </flux:dropdown>
</flux:table.cell>
                </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>

    {{-- LOAD MORE --}}
    @if($products->hasMorePages())
        <div class="flex justify-center pt-10">
            <flux:button wire:click="loadMore" variant="ghost" class="uppercase text-[10px] tracking-[0.5em] font-black italic" >
                Load More Matrix Data ↓
            </flux:button>
        </div>
    @endif
</div>
