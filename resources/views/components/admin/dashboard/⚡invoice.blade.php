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

<div class="p-10 max-w-7xl mx-auto space-y-8">

    {{-- HEADER --}}
    <div class="flex justify-between items-end border-b border-black dark:border-white pb-6">
        <div>
            <h1 class="text-2xl font-black uppercase italic tracking-tighter">
                Sales Ledger
            </h1>
            <p class="text-[10px] uppercase tracking-[0.4em] opacity-40">
                Transaction Registry / Stripe Sync
            </p>
        </div>

        {{-- FILTER --}}
        <select wire:model="status"
                class="border border-black dark:border-white bg-transparent text-xs uppercase font-black p-2">
            <option value="all">All</option>
            <option value="pending">Pending</option>
            <option value="paid">Paid</option>
            <option value="failed">Failed</option>
            <option value="refunded">Refunded</option>
        </select>
    </div>

    {{-- TABLE --}}
    <div class="overflow-x-auto border border-black dark:border-white">
        <table class="w-full text-sm">
            
            <thead class="bg-black text-white dark:bg-white dark:text-black">
                <tr class="text-left text-[10px] uppercase tracking-widest">
                    <th class="p-3">Order</th>
                    <th class="p-3">Customer</th>
                    <th class="p-3">Status</th>
                    <th class="p-3">Items</th>
                    <th class="p-3">Total</th>
                    <th class="p-3">Stripe</th>
                    <th class="p-3">Date</th>
                </tr>
            </thead>

            <tbody>
                @foreach($this->orders as $order)
                    <tr class="border-t border-zinc-200 dark:border-zinc-800">

                        {{-- ORDER NUMBER --}}
                        <td class="p-3 font-black uppercase text-xs">
                            {{ $order->order_number }}
                        </td>

                        {{-- CUSTOMER --}}
                        <td class="p-3 text-xs uppercase">
                            {{ $order->user->name ?? 'Guest' }}
                        </td>

                        {{-- STATUS --}}
                        <td class="p-3">
                            <span class="text-[10px] uppercase font-black px-2 py-1 border
                                {{ $order->status === 'paid' ? 'bg-green-500 text-white' : '' }}
                                {{ $order->status === 'pending' ? 'bg-yellow-300 text-black' : '' }}
                                {{ $order->status === 'failed' ? 'bg-red-500 text-white' : '' }}
                                {{ $order->status === 'refunded' ? 'bg-blue-500 text-white' : '' }}
                            ">
                                {{ $order->status }}
                            </span>
                        </td>

                        {{-- ITEMS --}}
                        <td class="p-3 text-[10px] uppercase opacity-70">
                            {{ $order->items->count() }} items
                        </td>

                        {{-- TOTAL --}}
                        <td class="p-3 font-mono text-xs font-black">
                            ${{ number_format($order->total_amount, 2) }}
                        </td>

                        {{-- STRIPE --}}
                        <td class="p-3 text-[9px] opacity-50">
                            {{ $order->stripe_session_id ?? '-' }}
                        </td>

                        {{-- DATE --}}
                        <td class="p-3 text-[9px] opacity-50">
                            {{ $order->created_at->format('Y-m-d H:i') }}
                        </td>

                    </tr>
                @endforeach
            </tbody>

        </table>
    </div>

</div>