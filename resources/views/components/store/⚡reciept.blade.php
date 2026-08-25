<?php

use Livewire\Component;
use App\Services\Store\CartService;
use App\Models\Order;

new class extends Component {
    public $orderNumber;

    public function mount(CartService $cart)
    {
        // 1. Identify the most recent paid order for this user to show the receipt
        $order = Order::where('user_id', auth()->id())
                      ->where('status', 'paid')
                      ->latest()
                      ->first();

        $this->orderNumber = $order ? $order->order_number : null;

        // 2. Wipe the local session cart
        $cart->clear();

        // 3. Dispatch the global reset signal to the Nav Bag
        $this->dispatch('cart-updated');
        
        // 4. Send UI notification
        $this->dispatch('notify', message: 'ARCHIVE SECURED / SELECTION CLEARED', type: 'success');
    }
}; ?>

<div class="bg-white text-black min-h-screen flex items-center justify-center px-4">
    <div class="max-w-md w-full border border-black p-10 text-center space-y-8">
        <div>
            <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#E31837] mb-2">Confirmed</p>
            <h1 class="text-2xl font-black tracking-tight">Payment Successful</h1>
        </div>

        <div class="py-6 border-y border-black space-y-1">
            <p class="text-[10px] font-bold uppercase tracking-widest text-zinc-500">Order Number</p>
            <p class="font-mono text-lg font-medium">{{ $orderNumber ?? 'Unavailable' }}</p>
        </div>

        <div class="flex flex-col gap-3">
            <flux:modal.trigger name="view-manifest">
                <button type="button" class="w-full h-12 text-[11px] font-bold uppercase tracking-[0.2em] bg-black text-white hover:bg-[#E31837] transition-colors">
                    View Order Details
                </button>
            </flux:modal.trigger>

            <a href="{{ route('store.all') }}" wire:navigate class="text-[11px] font-bold uppercase tracking-[0.2em] text-zinc-500 hover:text-black transition-colors">
                Continue Shopping
            </a>
        </div>
    </div>

    <flux:modal name="view-manifest" class="p-0 max-w-lg">
        @if($orderNumber)
            @livewire('store.order-manifest', ['orderNumber' => $orderNumber])
        @endif
    </flux:modal>
</div>
