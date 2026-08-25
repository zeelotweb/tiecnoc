<?php

use Livewire\Component;
use App\Models\Order;

new class extends Component {
    public $orderNumber;

    public function mount($orderNumber)
    {
        $this->orderNumber = $orderNumber;
    }

    public function getOrderProperty()
    {
        return Order::with('items')
            ->where('order_number', $this->orderNumber)
            ->first();
    }
}; ?>

<div class="bg-white text-black overflow-hidden">
    @if($this->order)
        <header class="p-6 border-b border-black">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#E31837] mb-1">Order</p>
                    <h2 class="text-xl font-black tracking-tight">{{ $this->order->order_number }}</h2>
                </div>

                <a href="{{ route('order.manifest.download', $this->order->order_number) }}"
                   class="h-9 px-3 inline-flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-widest border border-black hover:bg-black hover:text-white transition-colors">
                    <flux:icon.document-arrow-down class="w-4 h-4" />
                    PDF
                </a>
            </div>

            <div class="flex justify-between items-end mt-6">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-widest text-zinc-500 mb-1">Status</p>
                    <flux:badge size="sm" :color="match($this->order->status) {
                        'paid' => 'emerald',
                        'pending' => 'amber',
                        'refunded', 'void' => 'red',
                        default => 'zinc',
                    }">{{ ucfirst($this->order->status) }}</flux:badge>
                </div>
                <div class="text-right">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-zinc-500 mb-1">Date</p>
                    <p class="text-sm">{{ $this->order->created_at->format('M j, Y') }}</p>
                </div>
            </div>
        </header>

        <div class="p-6 space-y-6 max-h-[50vh] overflow-y-auto">
            @forelse($this->order->items as $item)
                <div class="flex gap-4 items-center border-b border-zinc-100 pb-6 last:border-0 last:pb-0">
                    <div class="w-16 h-16 bg-zinc-50 border border-black overflow-hidden shrink-0">
                        @if($item->image)
                            <img src="{{ Storage::url($item->image) }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-zinc-300">
                                <flux:icon.photo class="w-6 h-6" />
                            </div>
                        @endif
                    </div>

                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium truncate">{{ $item->name }}</p>
                        <p class="text-xs text-zinc-500 mt-0.5">{{ $item->attr }} &middot; Qty {{ $item->qty }}</p>
                    </div>

                    <span class="text-sm font-medium">${{ number_format((float) $item->price, 2) }}</span>
                </div>
            @empty
                <p class="py-12 text-center text-sm text-zinc-500">No items found for this order.</p>
            @endforelse
        </div>

        <footer class="p-6 border-t border-black flex justify-between items-center">
            <span class="text-[10px] font-bold uppercase tracking-widest text-zinc-500">Total</span>
            <span class="text-2xl font-black tracking-tight">${{ number_format((float) $this->order->total_amount, 2) }}</span>
        </footer>
    @else
        <div class="p-16 text-center text-sm text-zinc-500">
            Order not found.
        </div>
    @endif
</div>
