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

<div class="p-8 lg:p-24 max-w-7xl mx-auto space-y-16">

    <header class="border-b border-black dark:border-white pb-8">
        <flux:heading size="xl" class="uppercase font-black tracking-tighter italic">
            Your Selection / Archive
        </flux:heading>
    </header>

    @if($items->isEmpty())

        <div class="py-40 text-center opacity-20 uppercase tracking-[0.6em] text-[10px] font-black italic">
            Selection is currently empty.
        </div>

    @else

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-20">

            {{-- ITEMS --}}
            <div class="lg:col-span-2 space-y-12">

                @foreach($items as $item)

                    <div class="flex gap-8 border-b pb-12 group">

                        {{-- IMAGE --}}
                        <div class="w-28 aspect-[3/4] relative overflow-hidden bg-zinc-50 dark:bg-zinc-950 border">

                            @if($item->image)
                                <img src="{{ asset('storage/' . $item->image) }}"
                                     class="absolute inset-0 w-full h-full object-cover">
                            @endif

                        </div>

                        {{-- INFO --}}
                        <div class="flex-1 space-y-2">

                            <div class="flex justify-between items-start">

                                <h4 class="uppercase font-black text-sm tracking-tighter italic">
                                    {{ $item->name }}
                                </h4>

                                <flux:button
                                    variant="subtle"
                                    icon="x-mark"
                                    wire:click="removeItem({{ $item->id }})"
                                    size="xs"
                                />

                            </div>

                            <p class="text-[9px] uppercase tracking-widest font-black opacity-40 italic">
                                {{ $item->attr }}
                            </p>

                            <div class="pt-6 flex justify-between items-end">

                                <span class="text-[10px] uppercase font-black tracking-widest">
                                    Qty: {{ $item->qty }}
                                </span>

                                <span class="font-mono text-sm font-black italic">
                                    ${{ number_format($item->price * $item->qty, 2) }}
                                </span>

                            </div>

                            <livewire:platform.reaction_button
                                :variantId="$item->product_variant_id"
                            />

                        </div>

                    </div>

                @endforeach

            </div>

            {{-- SUMMARY --}}
            <div class="p-10 border border-black dark:border-white space-y-10 h-fit">

                <div class="border-b pb-4 uppercase text-[10px] font-black tracking-[0.4em] italic">
                    Order Logic
                </div>

                <div class="space-y-4">

                    <div class="flex justify-between text-[10px] uppercase font-black italic">
                        <span class="opacity-40">Subtotal</span>
                        <span class="font-mono">${{ number_format($total, 2) }}</span>
                    </div>

                    <div class="flex justify-between text-[10px] uppercase font-black italic">
                        <span class="opacity-40">Shipping</span>
                        <span>Calculated at checkout</span>
                    </div>

                </div>

                <form action="{{ route('checkout') }}" method="POST" class="pt-6 border-t">
                    @csrf

                    <button
                        type="submit"
                        class="w-full h-16 uppercase font-black tracking-[0.4em] text-[10px]
                        bg-black text-white dark:bg-white dark:text-black hover:bg-[#E31837] transition">
                        Initialize Payment →
                    </button>

                </form>

            </div>

        </div>

    @endif

</div>