<?php

use Livewire\Component;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Order; // Assuming you'll have an Order model soon

new class extends Component {
    public function with()
    {
        return [
            'total_revenue' => 12450.00, // Placeholder for Order::sum('total')
            'active_deals' => 3,        // Count of active member promotions
            'low_stock_count' => ProductVariant::whereColumn('stock_quantity', '<=', 'low_stock_threshold')->count(),
            'total_products' => Product::count(),
            'recent_activity' => [
                ['label' => 'New Product Added', 'meta' => 'Signature Hoodie', 'time' => '2 mins ago'],
                ['label' => 'Restocked', 'meta' => 'Logo Polo (Desert Sky/M)', 'time' => '1 hour ago'],
                ['label' => 'New Member Deal', 'meta' => 'Spring 20% Off', 'time' => '5 hours ago'],
            ]
        ];
    }
}; ?>

<div class="p-8 max-w-7xl mx-auto space-y-10">
    <flux:header>
        <flux:heading size="xl">Admin Overview</flux:heading>
        <flux:subheading>Welcome back to the Tiecnoc command center.</flux:subheading>
    </flux:header>

    {{-- 1. Key Performance Metrics --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <flux:card class="flex flex-col justify-center items-center text-center p-6">
            <flux:text size="sm" color="zinc" class="uppercase tracking-widest font-semibold">Total Revenue</flux:text>
            <flux:heading size="xl" class="mt-2">${{ number_format($total_revenue, 2) }}</flux:heading>
        </flux:card>

        <flux:card class="flex flex-col justify-center items-center text-center p-6 border-l-4 border-red-500">
            <flux:text size="sm" color="zinc" class="uppercase tracking-widest font-semibold">Stock Alerts</flux:text>
            <flux:heading size="xl" class="mt-2 text-red-600">{{ $low_stock_count }} items</flux:heading>
            <flux:button variant="ghost" size="xs" class="mt-2" href="#">Restock Now</flux:button>
        </flux:card>

        <flux:card class="flex flex-col justify-center items-center text-center p-6 border-l-4 border-blue-500">
            <flux:text size="sm" color="zinc" class="uppercase tracking-widest font-semibold">Live Products</flux:text>
            <flux:heading size="xl" class="mt-2">{{ $total_products }}</flux:heading>
        </flux:card>

        <flux:card class="flex flex-col justify-center items-center text-center p-6 border-l-4 border-emerald-500">
            <flux:text size="sm" color="zinc" class="uppercase tracking-widest font-semibold">Member Deals</flux:text>
            <flux:heading size="xl" class="mt-2">{{ $active_deals }} Active</flux:heading>
        </flux:card>
    </div>

    {{-- 2. Gateway Actions & Recent Logs --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Quick Actions --}}
        <div class="lg:col-span-1 space-y-4">
            <flux:heading size="lg">Quick Gateways</flux:heading>
            <flux:button variant="filled" class="w-full justify-start" icon="plus" href="{{ route('admin.merchandise.create') }}">
                Add New Merchandise
            </flux:button>
            <flux:button variant="outline" class="w-full justify-start" icon="ticket">
                Create Member Deal
            </flux:button>
            <flux:button variant="outline" class="w-full justify-start" icon="archive-box">
                Inventory Report
            </flux:button>
        </div>

        {{-- Audit Log / Activity --}}
        <flux:card class="lg:col-span-2">
            <flux:heading size="lg" class="mb-4">Recent Platform Activity</flux:heading>
            <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @foreach($recent_activity as $log)
                    <div class="py-4 flex justify-between items-center">
                        <div>
                            <flux:text weight="semibold" class="text-zinc-900">{{ $log['label'] }}</flux:text>
                            <flux:text size="sm" color="zinc">{{ $log['meta'] }}</flux:text>
                        </div>
                        <flux:text size="xs" color="zinc">{{ $log['time'] }}</flux:text>
                    </div>
                @endforeach
            </div>
        </flux:card>
    </div>
</div>
