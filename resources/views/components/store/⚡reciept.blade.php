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

<div class="min-h-[60vh] flex items-center justify-center bg-white dark:bg-black p-8">
    <div class="max-w-md w-full border-2 border-black dark:border-white p-12 text-center space-y-8 animate-in fade-in zoom-in duration-700">
        <header class="space-y-2">
            <flux:heading size="xl" class="uppercase font-black italic tracking-tighter">Transaction Verified</flux:heading>
            <p class="text-[10px] uppercase tracking-[0.3em] opacity-40 font-black">Native Activity Recorded</p>
        </header>

        <div class="py-8 border-y border-black/10 dark:border-white/10 space-y-2">
            <p class="text-[8px] uppercase font-black opacity-30 tracking-widest">Manifest ID</p>
            <p class="font-mono text-lg font-bold">{{ $orderNumber ?? 'TNC-UNKNOWN' }}</p>
        </div>

        <div class="flex flex-col gap-3">
            {{-- This would link to your Finance/Ledger or a dedicated User Receipt page --}}
               {{-- MODAL TRIGGER --}}
            <flux:modal.trigger name="view-manifest">
                <flux:button variant="filled" class="w-full bg-black text-white dark:bg-white dark:text-black rounded-none uppercase font-black italic tracking-tighter h-14">
                    View Digital Manifest
                </flux:button>
            </flux:modal.trigger>

            <flux:button href="{{ route('store.all') }}" variant="subtle" class="uppercase font-black text-[9px] tracking-widest opacity-50 hover:opacity-100 transition-opacity">
                Return to Catalogue
            </flux:button>
        </div>

        <footer class="pt-4">
            <p class="text-[7px] uppercase tracking-[0.5em] font-black opacity-20 italic">Surgically Optimized Fulfillment in Progress</p>
        </footer>
    </div>

        {{-- THE MANIFEST MODAL --}}
    <flux:modal name="view-manifest" class="p-0 border-2 border-black dark:border-white rounded-none max-w-lg">
        @if($orderNumber)
            @livewire('store.order-manifest', ['orderNumber' => $orderNumber])
        @endif
    </flux:modal>
</div>
