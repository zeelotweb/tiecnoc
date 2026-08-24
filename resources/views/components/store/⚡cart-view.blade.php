<?php

use Livewire\Component;
use App\Services\Store\CartService;
use App\Models\OrderItem;

new class extends Component {

    /**
     * REMOVE ITEM
     */
    public function removeItem($itemId, CartService $cart)
    {
        try {
            $item = OrderItem::with('order')->findOrFail($itemId);
            $order = $item->order;

            $item->delete();

            // SAFE total recalculation
            $newTotal = $order->items()
                ->selectRaw('SUM(price * qty) as total')
                ->value('total') ?? 0;

            $order->update([
                'total_amount' => $newTotal
            ]);

            if ($order->items()->count() === 0) {
                $order->delete();
            }

            $this->dispatch('cart-updated');
            $this->dispatch('notify', message: 'SELECTION REMOVED', type: 'error');

        } catch (\Exception $e) {
            $this->dispatch('notify', message: 'PURGE ERROR', type: 'error');
        }
    }

    /**
     * DATA SOURCE (single source of truth)
     */
    public function with(CartService $cart)
    {
        return [
            'items' => $cart->getItems(),
            'total' => $cart->total()
        ];
    }
};
?>

<div class="bg-white text-black min-h-screen">
    <div class="max-w-7xl mx-auto px-4 lg:px-8 py-10 space-y-10">

        <div class="border-b border-black pb-6">
            <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#E31837] mb-2">Selection</p>
            <h1 class="text-3xl md:text-4xl font-black tracking-tight leading-none">Bag</h1>
        </div>

        @if($items->isEmpty())

            <div class="py-24 text-center text-sm text-zinc-500 border border-dashed border-zinc-300">
                Your bag is empty.
            </div>

        @else

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-16">

                {{-- ITEMS --}}
                <div class="lg:col-span-2 divide-y divide-black/10">

                    @foreach($items as $item)

                        <div class="flex gap-6 py-8 first:pt-0">

                            {{-- IMAGE --}}
                            <div class="w-28 aspect-[3/4] relative overflow-hidden bg-zinc-50 border border-black shrink-0">
                                @if($item->image)
                                    <img src="{{ asset('storage/' . $item->image) }}"
                                         class="absolute inset-0 w-full h-full object-cover">
                                @endif
                            </div>

                            {{-- INFO --}}
                            <div class="flex-1 space-y-3">

                                <div class="flex justify-between items-start">
                                    <p class="font-medium text-sm">{{ $item->name }}</p>
                                    <flux:button
                                        variant="ghost"
                                        icon="x-mark"
                                        wire:click="removeItem({{ $item->id }})"
                                        size="xs"
                                    />
                                </div>

                                <p class="text-xs text-zinc-500">{{ $item->attr }}</p>

                                <div class="pt-4 flex justify-between items-end">
                                    <span class="text-sm text-zinc-500">Qty {{ $item->qty }}</span>
                                    <span class="text-sm font-medium">${{ number_format($item->price * $item->qty, 2) }}</span>
                                </div>

                                <livewire:platform.reaction_button
                                    :variantId="$item->product_variant_id"
                                />
                            </div>

                        </div>

                    @endforeach

                </div>

                {{-- SUMMARY --}}
                <div class="p-6 border border-black space-y-6 h-fit">

                    <p class="text-sm font-medium border-b border-black pb-4">Order Summary</p>

                    <div class="space-y-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-zinc-500">Subtotal</span>
                            <span>${{ number_format($total, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-zinc-500">Shipping</span>
                            <span class="text-zinc-500">Calculated at checkout</span>
                        </div>
                    </div>

                    <form action="{{ route('checkout') }}" method="POST" class="pt-4 border-t border-black">
                        @csrf
                        <button
                            type="submit"
                            class="w-full h-14 text-[11px] font-bold uppercase tracking-[0.2em]
                            bg-black text-white hover:bg-[#E31837] transition-colors">
                            Checkout
                        </button>
                    </form>

                </div>

            </div>

        @endif

    </div>
</div>