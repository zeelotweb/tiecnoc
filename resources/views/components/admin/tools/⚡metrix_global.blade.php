<?php

use Livewire\Component;
use App\Models\Product;
use App\Models\ProductColor;
use App\Models\ProductVariant;
use App\Models\Order;
use App\Models\OrderItem;

new class extends Component {

    public function with(): array
    {
        /*
        |--------------------------------------------------------------------------
        | SALES (WHAT'S ACTUALLY MOVING)
        |--------------------------------------------------------------------------
        */
        $paidOrders = Order::where('status', 'paid');

        $revenue = (clone $paidOrders)->sum('total_amount');
        $orderCount = (clone $paidOrders)->count();

        $unitsSold = OrderItem::whereHas('order', fn ($q) => $q->where('status', 'paid'))->sum('qty');

        $topSellers = OrderItem::whereHas('order', fn ($q) => $q->where('status', 'paid'))
            ->selectRaw('name, attr, SUM(qty) as units, SUM(qty * price) as revenue')
            ->groupBy('name', 'attr')
            ->orderByDesc('units')
            ->take(5)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | STOCK HEALTH
        |--------------------------------------------------------------------------
        */
        $lowStock = ProductVariant::with('color.product')
            ->where('stock_quantity', '<=', 5)
            ->orderBy('stock_quantity')
            ->take(6)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | CATALOG SIZE (CONTEXT ONLY)
        |--------------------------------------------------------------------------
        */
        $products = Product::count();
        $colorsLive = ProductColor::where('status', 'live')->count();

        return [
            'revenue' => $revenue,
            'orderCount' => $orderCount,
            'unitsSold' => $unitsSold,
            'topSellers' => $topSellers,
            'lowStock' => $lowStock,
            'products' => $products,
            'colorsLive' => $colorsLive,
        ];
    }
};
?>

<div class="space-y-10">

    {{-- SALES --}}
    <div class="grid grid-cols-3 gap-4">
        <div class="border border-black/15 dark:border-white/15 p-4">
            <p class="text-xs text-zinc-500 mb-1">Revenue</p>
            <p class="text-2xl font-semibold text-zinc-900 dark:text-white">${{ number_format($revenue, 2) }}</p>
        </div>
        <div class="border border-black/15 dark:border-white/15 p-4">
            <p class="text-xs text-zinc-500 mb-1">Paid Orders</p>
            <p class="text-2xl font-semibold text-zinc-900 dark:text-white">{{ $orderCount }}</p>
        </div>
        <div class="border border-black/15 dark:border-white/15 p-4">
            <p class="text-xs text-zinc-500 mb-1">Units Sold</p>
            <p class="text-2xl font-semibold text-zinc-900 dark:text-white">{{ $unitsSold }}</p>
        </div>
    </div>

    {{-- TOP SELLERS --}}
    <div class="space-y-3">
        <p class="text-sm font-medium">Top Sellers</p>
        <div class="border border-black/15 dark:border-white/15 divide-y divide-black/15 dark:divide-white/15">
            @forelse($topSellers as $item)
                <div class="flex items-center justify-between px-4 py-2.5 text-sm">
                    <span class="font-medium">{{ $item->name }} <span class="text-zinc-500 font-normal">{{ $item->attr }}</span></span>
                    <span class="text-zinc-500">{{ $item->units }} sold · ${{ number_format($item->revenue, 2) }}</span>
                </div>
            @empty
                <div class="px-4 py-8 text-center text-sm text-zinc-500">Nothing sold yet.</div>
            @endforelse
        </div>
    </div>

    {{-- STOCK HEALTH --}}
    <div class="space-y-3">
        <p class="text-sm font-medium">Stock Health</p>
        <div class="border border-black/15 dark:border-white/15 divide-y divide-black/15 dark:divide-white/15">
            @forelse($lowStock as $variant)
                <div class="flex items-center justify-between px-4 py-2.5 text-sm">
                    <span class="text-zinc-600 dark:text-zinc-400">{{ $variant->product?->name }} · {{ $variant->color_name }} / {{ $variant->size }}</span>
                    <span class="font-medium {{ $variant->stock_quantity <= 0 ? 'text-[#E31837]' : 'text-amber-600 dark:text-amber-400' }}">
                        {{ $variant->stock_quantity <= 0 ? 'Out' : $variant->stock_quantity }}
                    </span>
                </div>
            @empty
                <div class="px-4 py-8 text-center text-sm text-zinc-500">Everything's stocked.</div>
            @endforelse
        </div>
    </div>

    {{-- CATALOG SIZE (footer context) --}}
    <p class="text-xs text-zinc-500">{{ $products }} products in the studio · {{ $colorsLive }} colorways on the floor</p>

</div>
