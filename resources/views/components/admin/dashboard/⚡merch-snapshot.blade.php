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

<section class="space-y-3 gothic-theme">
    {{-- Header with Toggle --}}
    <div class="flex-col items-end border-b border-black dark:border-white pb-4">
        <flux:heading size="lg" class="uppercase tracking-tighter italic font-black">
            Catalog Matrix / Snapshot
        </flux:heading>

        <div class="flex">
            <p class="text-[10px] uppercase tracking-[0.4em] italic mt-1 {{ $viewLive ? 'text-emerald-500 font-bold opacity-100' : 'opacity-50' }}">
                Currently Viewing: {{ $viewLive ? 'Live Storefront' : 'Staging (Drafts)' }}
            </p>
        </div>

<div class="flex items-center gap-3 mt-4 justify-end">
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

    {{-- Matrix Table --}}
    <flux:table>
        <flux:table.columns class="w-full">
            <flux:table.column class="uppercase text-[9px] tracking-[0.4em] font-black">Identity</flux:table.column>
            <flux:table.column class="uppercase text-[9px] tracking-[0.4em] font-black">Retail</flux:table.column>
            <flux:table.column class="hidden uppercase text-[9px] tracking-[0.4em] font-black"> Status </flux:table.column>
            <flux:table.column class="uppercase text-[9px] tracking-[0.4em] font-black text-right">Tools</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse($products as $product)
                @php
                    $canGoLive = $product->colors->contains(fn($c) => !empty($c->front_image_path)) && 
                                 $product->colors->contains(fn($c) => $c->variants->count() > 0);
                @endphp
                <flux:table.row :key="$product->id" class="group">
                    <flux:table.cell class="font-medium uppercase text-sm italic">{{ $product->name }}</flux:table.cell>
                    <flux:table.cell class="text-zinc-500">${{ number_format($product->base_price, 2) }}</flux:table.cell>
                    
                    <flux:table.cell hidden>
                        <div class="hidden flex items-center gap-2 justify-center">
                            <flux:label class="text-[8px] font-black uppercase {{ $product->status === 'draft' ? 'opacity-100' : 'opacity-20' }}"> Draft </flux:label>
                            
                            {{-- Visual Lock Applied here --}}
                            <flux:switch 
                                :checked="$product->status === 'live'" 
                                wire:click="toggleStatus({{ $product->id }})" 
                                class="{{ $product->status === 'live' ? '[--switch-color:theme(colors.emerald.400)]' : ($canGoLive ? 'opacity-40' : 'opacity-10 pointer-events-none') }}"
                            />
                            
                            <flux:label class="text-[8px] font-black uppercase {{ $product->status === 'live' ? 'text-emerald-500 font-bold opacity-100' : 'opacity-20' }}"> Live </flux:label>
                        </div>
                    </flux:table.cell>

@php
    $user = auth()->user();

    $isAdmin = $user->isSuperAdmin() || $user->isAdmin();

    $tools = $user->tools?->pluck('tool')->toArray() ?? [];

    // 🔒 trigger rule
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

            {{-- ACTIONS (PERMISSION BASED) --}}

            @if($can('media'))
                <flux:menu.item icon="photo"
                    class="uppercase text-[10px] font-black tracking-widest"
                    x-on:click="$dispatch('load-visuals-tool', { id: {{ $product->id }} }); $flux.modal('media-modal').show();">
                    Add Visuals
                </flux:menu.item>
            @endif

            @if($can('specs'))
                <flux:menu.item icon="swatch"
                    class="uppercase text-[10px] font-black tracking-widest"
                    x-on:click="$dispatch('load-specs-tool', { id: {{ $product->id }} }); $flux.modal('specs-modal').show();">
                    Manage Specs
                </flux:menu.item>
            @endif

            @if($can('editor'))
                <flux:menu.item icon="pencil-square"
                    class="uppercase text-[10px] font-black tracking-widest"
                    x-on:click="$dispatch('load-editor-tool', { id: {{ $product->id }} }); $flux.modal('edit-modal').show();">
                    Edit Info
                </flux:menu.item>
            @endif

            @if($can('metrics') || $can('gallery') || $can('toggle'))
                <flux:menu.separator class="bg-black/10 dark:bg-white/10" />
            @endif

            @if($can('metrics'))
                <flux:menu.item icon="chart-bar"
                    class="uppercase text-[10px] font-black tracking-widest"
                    x-on:click="$dispatch('load-metrics-tool', { id: {{ $product->id }} }); $flux.modal('metrics-modal').show();">
                    Metrics
                </flux:menu.item>
            @endif

            @if($can('gallery'))
                <flux:menu.item icon="photo"
                    class="uppercase text-[10px] font-black tracking-widest"
                    x-on:click="$dispatch('media-gallery-modal', { id: {{ $product->id }} }); setTimeout(() => { window.initProductGalleryPond(); }, 100); $flux.modal('media-gallery-modal').show();">
                    Add Media
                </flux:menu.item>
            @endif

            @if($can('toggle'))
                <flux:menu.item icon="bolt"
                    class="uppercase text-[10px] font-black tracking-widest"
                    x-on:click="$dispatch('load-color-toggle-tool', { id: {{ $product->id }} }); $flux.modal('color-toggle-modal').show();">
                    Toggle Availability
                </flux:menu.item>
            @endif

            {{-- ALWAYS VISIBLE --}}
            <flux:menu.separator class="bg-black/10 dark:bg-white/10" />

            <flux:menu.item icon="photo"
                class="uppercase text-[10px] font-black tracking-widest"
                x-on:click="$dispatch('load-media-tool', { id: {{ $product->id }} }); $flux.modal('load-media-tool').show();">
                View Media
            </flux:menu.item>

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
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="4" class="text-center py-10 uppercase text-[10px] tracking-widest opacity-50 italic"> The matrix is currently empty </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    <div class="flex justify-center border-t border-black/5 pt-6">
        <flux:button variant="ghost" class="uppercase text-[10px] tracking-[0.3em] font-black italic" href="{{ route('admin.merchandise.index') }}"> Full Matrix Catalog → </flux:button>
    </div>
</section>
