<x-layouts::app :title="__('Checkout Cancelled')">
    <div class="bg-white text-black min-h-screen flex items-center justify-center">
        <div class="max-w-sm w-full text-center px-4 space-y-6">
            <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#E31837]">Checkout</p>
            <h1 class="text-3xl font-black tracking-tight">Order Not Completed</h1>
            <p class="text-sm text-zinc-500">
                Your payment was cancelled and no charge was made. Your bag is still saved.
            </p>
            <a href="{{ route('store.cart') }}" wire:navigate
               class="block h-12 leading-[3rem] text-[11px] font-bold uppercase tracking-[0.2em] bg-black text-white hover:bg-[#E31837] transition-colors">
                Return to Bag
            </a>
        </div>
    </div>
</x-layouts::app>
