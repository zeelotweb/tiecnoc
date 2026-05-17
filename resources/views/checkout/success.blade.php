<x-layouts::app :title="__('Dashboard')">

    <div class="min-h-screen flex flex-col items-center justify-center p-12 text-center space-y-8 animate-in fade-in duration-1000 hidden">
        <header class="space-y-2">
            <flux:heading size="xl" class="uppercase font-black italic tracking-tighter">Archive / Paid</flux:heading>
            <p class="text-[9px] uppercase tracking-[0.5em] opacity-40 font-black italic">Transaction Identity Confirmed</p>
        </header>

        <div class="max-w-xs space-y-6">
            <p class="text-[11px] leading-relaxed opacity-70">
                Acknowledge: Payment verified for <span class="font-black italic underline">{{ $customer }}</span>. 
                The selection has been moved to the fulfillment queue.
            </p>
            
            <div class="pt-8 border-t border-black/10">
                <flux:button href="{{ route('store.all') }}" variant="subtle" class="w-full bg-black text-white rounded-none h-12 uppercase font-black tracking-widest text-[10px] hover:invert transition-all">
                    Return to Selection
                </flux:button>
            </div>


        </div>


<x-ui.glass class="p-4 max-w-xl mx-auto text-center space-y-6">

    <h1 class="text-xl font-black uppercase italic">
        Payment Confirmed
    </h1>

    <p class="text-xs uppercase tracking-widest opacity-50">
        Thank you, {{ $customer }}
    </p>

</x-ui.glass>

        <footer class="pt-12 opacity-20">
            <p class="text-[8px] uppercase tracking-[0.4em] font-black italic">TNC-AUTO-FULFILLMENT-ACTIVE</p>
        </footer>
    </div>

  <livewire:store.reciept />
</x-layouts::app>





