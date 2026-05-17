<?php

use Livewire\Component;
use App\Models\Order;

new class extends Component {
    public $orderNumber;

    public function mount($orderNumber)
    {
        $this->orderNumber = $orderNumber;
    }

    /**
     * TNC / AUTHORITY: NATIVE ORDER DATA
     * Computed property ensures the items are eager-loaded for the manifest grid.
     */
    public function getOrderProperty()
    {
        return Order::with('items')
            ->where('order_number', $this->orderNumber)
            ->first();
    }
}; ?>

<div class="p-0 bg-white dark:bg-black text-black dark:text-white border border-black dark:border-white overflow-hidden">
    @if($this->order)
        <header class="p-8 border-b border-black dark:border-white bg-zinc-50 dark:bg-zinc-900">
            <div class="flex justify-between items-start">
                <div>
                    <flux:heading size="xl" class="uppercase font-black italic tracking-tighter">Digital Manifest</flux:heading>
                    <p class="text-[9px] uppercase tracking-[0.4em] font-black opacity-40 italic mt-1">Verified Platform Record</p>
                </div>
                
                {{-- PDF EXPORT TRIGGER --}}
                <flux:button 
                    href="{{ route('admin.order.manifest.download', $this->order->order_number) }}" 
                    variant="subtle" 
                    icon="document-arrow-down" 
                    class="uppercase font-black text-[8px] border border-black dark:border-white rounded-none h-10 px-4"
                >
                    Export PDF
                </flux:button>
            </div>

            <div class="flex justify-between items-end mt-8">
                <div>
                    <p class="text-[8px] uppercase font-black opacity-30 tracking-widest">Status Flag</p>
                    <p @class([
                        'text-[10px] uppercase font-black px-2 py-0.5 border inline-block mt-1',
                        'bg-green-500 text-white border-green-500' => $this->order->status === 'paid',
                        'bg-zinc-100 text-black border-black opacity-40' => $this->order->status === 'pending',
                        'bg-red-500 text-white border-red-500' => in_array($this->order->status, ['refunded', 'void']),
                    ])>{{ $this->order->status }}</p>
                </div>
                <div class="text-right">
                    <p class="text-[8px] uppercase font-black opacity-30 tracking-widest">Identity ID</p>
                    <p class="text-[11px] uppercase font-black font-mono tracking-tighter">{{ $this->order->order_number }}</p>
                </div>
            </div>
        </header>

        <div class="p-8 space-y-6 max-h-[50vh] overflow-y-auto">
            {{-- Loop through native items --}}
            @forelse($this->order->items as $item)
                <div class="flex gap-6 items-center group border-b border-zinc-100 dark:border-zinc-900 pb-6 last:border-0 last:pb-0">
                    {{-- Visual Snapshot --}}
                    <div class="w-20 h-20 bg-zinc-100 dark:bg-zinc-900 border border-black overflow-hidden flex-shrink-0">
                        @if($item->image)
                            <img src="{{ Storage::url($item->image) }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
                        @else
                            <div class="w-full h-full flex items-center justify-center opacity-10">
                                <flux:icon.photo class="w-8 h-8" />
                            </div>
                        @endif
                    </div>

                    <div class="flex-1 min-w-0">
                        <p class="text-[11px] uppercase font-black tracking-widest truncate">{{ $item->name }}</p>
                        <p class="text-[9px] font-mono opacity-50 uppercase mt-1">{{ $item->sku }} // {{ $item->attr }}</p>
                        <p class="text-[8px] uppercase font-black tracking-widest mt-2 px-2 py-0.5 bg-zinc-100 dark:bg-zinc-800 inline-block">Units: {{ $item->qty }}</p>
                    </div>

                    <div class="text-right">
                        <p class="text-sm font-black italic tracking-tighter">${{ number_format((float)$item->price, 2) }}</p>
                        <p class="text-[7px] opacity-30 uppercase font-black tracking-widest mt-1">Item Valuation</p>
                    </div>
                </div>
            @empty
                <div class="py-20 text-center opacity-30 uppercase text-[10px] font-black italic tracking-[0.5em]">
                    Manifest Items Null / Data Sync Failure
                </div>
            @endforelse
        </div>

        <footer class="p-8 border-t border-black dark:border-white bg-zinc-50 dark:bg-zinc-900 flex justify-between items-end">
            <div class="space-y-1">
                <div class="opacity-20 uppercase font-black text-[7px] tracking-[0.4em]">
                    Digital Record Authority
                </div>
                <div class="text-[8px] font-mono opacity-40 uppercase">
                    TS: {{ $this->order->created_at->format('Y.m.d.H.i.s') }}
                </div>
            </div>
            
            <div class="text-right">
                <p class="text-[8px] uppercase font-black opacity-30 tracking-widest">Aggregate Total</p>
                <p class="text-4xl font-black italic tracking-tighter leading-none mt-1">${{ number_format((float)$this->order->total_amount, 2) }}</p>
            </div>
        </footer>
    @else
        <div class="p-32 text-center flex flex-col items-center justify-center space-y-4">
            <flux:icon.document-magnifying-glass class="w-12 h-12 opacity-10" />
            <p class="uppercase font-black opacity-20 text-[10px] tracking-[0.5em]">Manifest Context Not Resolved</p>
        </div>
    @endif
</div>
