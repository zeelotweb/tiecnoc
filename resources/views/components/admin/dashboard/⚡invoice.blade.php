<?php

use Livewire\Component;
use App\Models\Order;

new class extends Component {

    public $status = 'all';

    public function getOrdersProperty()
    {
        return Order::with('items')
            ->when($this->status !== 'all', function ($q) {
                $q->where('status', $this->status);
            })
            ->latest()
            ->get();
    }
};
?>

<div class="space-y-6">

    {{-- FILTER --}}
    <div class="flex justify-end">
        <flux:select wire:model.live="status" class="w-40">
            <flux:select.option value="all">All Orders</flux:select.option>
            <flux:select.option value="pending">Pending</flux:select.option>
            <flux:select.option value="paid">Paid</flux:select.option>
            <flux:select.option value="failed">Failed</flux:select.option>
            <flux:select.option value="refunded">Refunded</flux:select.option>
        </flux:select>
    </div>

    {{-- TABLE --}}
    <div class=" border border-black/15 dark:border-white/15 overflow-hidden">
    <flux:table>
        <flux:table.columns>
            <flux:table.column>Order</flux:table.column>
            <flux:table.column>Customer</flux:table.column>
            <flux:table.column>Status</flux:table.column>
            <flux:table.column>Items</flux:table.column>
            <flux:table.column>Total</flux:table.column>
            <flux:table.column>Date</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse($this->orders as $order)
                @php
                    $statusColor = match($order->status) {
                        'paid' => 'emerald',
                        'pending' => 'amber',
                        'failed' => 'red',
                        'refunded' => 'blue',
                        default => 'zinc',
                    };
                @endphp
                <flux:table.row :key="$order->id">
                    <flux:table.cell class="font-medium">{{ $order->order_number }}</flux:table.cell>
                    <flux:table.cell class="text-zinc-500">{{ $order->user->name ?? 'Guest' }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:badge :color="$statusColor" size="sm">{{ ucfirst($order->status) }}</flux:badge>
                    </flux:table.cell>
                    <flux:table.cell class="text-zinc-500">{{ $order->items->count() }}</flux:table.cell>
                    <flux:table.cell>${{ number_format($order->total_amount, 2) }}</flux:table.cell>
                    <flux:table.cell class="text-zinc-500">{{ $order->created_at->format('M j, Y') }}</flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="6" class="text-center py-12 text-zinc-500">No orders yet.</flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>
    </div>

</div>