<?php
use App\Models\{Product, ProductColor, ProductVariant};

$lowStockThreshold = 5;
$totalProducts = Product::count();
$liveColors = ProductColor::where('status', 'live')->count();
$lowStockCount = ProductVariant::where('stock_quantity', '<=', $lowStockThreshold)->where('stock_quantity', '>', 0)->count();
$outOfStockCount = ProductVariant::where('stock_quantity', '<=', 0)->count();
$draftCount = Product::whereDoesntHave('colors', fn ($q) => $q->where('status', 'live'))->count();

$attentionVariants = ProductVariant::with('color.product')
    ->where('stock_quantity', '<=', $lowStockThreshold)
    ->orderBy('stock_quantity')
    ->take(5)
    ->get();
?>
<x-layouts::admin>
    <x-slot:pageEyebrow>{{ __('Tiecnoc Studio') }}</x-slot:pageEyebrow>
    <x-slot:pageTitle>{{ __('Overview') }}</x-slot:pageTitle>
    <x-slot:pageActions>
        @if(auth()->user()->isAdmin())
            <div onclick="openFluxModal('add-product-modal')" class="inline-flex">
                <flux:button icon="plus" variant="primary">
                    Add Product
                </flux:button>
            </div>
        @endif
    </x-slot:pageActions>

    {{-- COMPACT STAT LINE --}}
    <div class="flex flex-wrap items-center gap-x-2 gap-y-1 text-sm text-zinc-500">
        <span><span class="font-medium text-zinc-900 dark:text-white">{{ $totalProducts }}</span> products</span>
        <span class="text-zinc-300 dark:text-zinc-700">·</span>
        <span><span class="font-medium text-zinc-900 dark:text-white">{{ $liveColors }}</span> live colorways</span>
        <span class="text-zinc-300 dark:text-zinc-700">·</span>
        <span class="{{ $lowStockCount > 0 ? 'text-amber-600 dark:text-amber-400' : '' }}"><span class="font-medium {{ $lowStockCount > 0 ? '' : 'text-zinc-900 dark:text-white' }}">{{ $lowStockCount }}</span> low stock</span>
        <span class="text-zinc-300 dark:text-zinc-700">·</span>
        <span class="{{ $outOfStockCount > 0 ? 'text-[#E31837]' : '' }}"><span class="font-medium {{ $outOfStockCount > 0 ? '' : 'text-zinc-900 dark:text-white' }}">{{ $outOfStockCount }}</span> out of stock</span>
    </div>

    {{-- MAIN LAYOUT --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        {{-- RECENT PRODUCTS (main content) --}}
        <div class="lg:col-span-2 space-y-3">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium">On the Line</p>
                <a href="{{ route('admin.merchandise.index') }}" wire:navigate class="text-xs font-medium text-[#E31837] hover:underline">View All</a>
            </div>
            @livewire('admin.dashboard.merch-snapshot')
        </div>

        {{-- RAIL --}}
        <div class="space-y-8">

            {{-- NEEDS ATTENTION --}}
            <div class="space-y-3">
                <p class="text-sm font-medium">Needs Attention</p>

                <div class="space-y-1">
                    @forelse($attentionVariants as $variant)
                        <div class="flex items-center justify-between py-1.5 text-sm border-b border-zinc-100 dark:border-zinc-900 last:border-0">
                            <span class="text-zinc-600 dark:text-zinc-400 truncate pr-2">
                                {{ $variant->product?->name }} · {{ $variant->color_name }} / {{ $variant->size }}
                            </span>
                            <span class="shrink-0 font-medium {{ $variant->stock_quantity <= 0 ? 'text-[#E31837]' : 'text-amber-600 dark:text-amber-400' }}">
                                {{ $variant->stock_quantity <= 0 ? 'Out' : $variant->stock_quantity }}
                            </span>
                        </div>
                    @empty
                        <p class="text-sm text-zinc-500">Nothing needs attention.</p>
                    @endforelse

                    @if($draftCount > 0)
                        <div class="flex items-center justify-between py-1.5 text-sm">
                            <span class="text-zinc-600 dark:text-zinc-400">Draft products</span>
                            <span class="font-medium text-zinc-900 dark:text-white">{{ $draftCount }}</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- LINKS --}}
            <div class="space-y-1">
                <a href="{{ route('admin.orders') }}" wire:navigate class="flex items-center gap-2 py-1.5 text-sm text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition-colors">
                    <flux:icon.receipt-percent variant="micro" class="shrink-0" />
                    Orders
                </a>
                <a href="{{ route('admin.reports') }}" wire:navigate class="flex items-center gap-2 py-1.5 text-sm text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition-colors">
                    <flux:icon.chart-bar variant="micro" class="shrink-0" />
                    Reports
                </a>
                @if(auth()->user()->isAdmin())
                    <a href="{{ route('admin.team') }}" wire:navigate class="flex items-center gap-2 py-1.5 text-sm text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition-colors">
                        <flux:icon.user-group variant="micro" class="shrink-0" />
                        Team
                    </a>
                @endif
            </div>

        </div>

    </div>

    @include('partials.admin.product-create-modal')
    @include('partials.admin.product-tools-modal')
</x-layouts::admin>
