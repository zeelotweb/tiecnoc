<?php

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\{Product, ProductVariant};
use App\Services\Admin\MerchandiseService;

new class extends Component {
    public $product_id = null;
    public $selected_variant_id = null;
    public $front_path = '', $back_path = '';

    #[On('load-visuals-tool')]
    public function init($id) {
        $this->product_id = $id;
        $this->reset(['selected_variant_id', 'front_path', 'back_path']);
    }

    public function selectVariant($id) {
        $this->selected_variant_id = $id;
        $variant = ProductVariant::find($id);
        $this->front_path = $variant->front_image_path;
        $this->back_path = $variant->back_image_path;
        
        // Re-init FilePond for the new selection
        $this->dispatch('reset-ponds');
    }

    public function saveColorVisuals(MerchandiseService $service) {
        $variant = ProductVariant::findOrFail($this->selected_variant_id);
        
        $variant->update([
            'front_image_path' => $this->front_path,
            'back_image_path' => $this->back_path,
        ]);

        $this->dispatch('notify', message: 'COLORWAY SYNCED', type: 'success');
    }

    public function with() {
        return [
            'variants' => $this->product_id 
                ? ProductVariant::where('product_id', $this->product_id)->get()->unique('color_name') 
                : []
        ];
    }
}; ?>

<div class="p-8 gothic-theme">
    @if($product_id)
        <flux:header class="border-b border-black pb-4 mb-8 dark:border-white">
            <flux:heading size="xl" class="uppercase font-black italic tracking-tighter">02 / Colorway Sync</flux:heading>
        </flux:header>

        {{-- 1. Color Selector Grid --}}
        <div class="space-y-4 mb-10">
            <flux:label class="uppercase text-[10px] font-black tracking-widest italic">Select Colorway to Map</flux:label>
            <div class="flex flex-wrap gap-4">
                @foreach($variants as $v)
                    <button 
                        wire:click="selectVariant({{ $v->id }})"
                        class="w-12 h-12 border-2 transition-all {{ $selected_variant_id == $v->id ? 'border-black opacity-100 scale-110' : 'border-transparent opacity-30 hover:opacity-60' }}"
                        style="background-color: {{ $v->hex_code ?? '#000' }}"
                        title="{{ $v->color_name }}"
                    ></button>
                @endforeach
                
                {{-- Quick Add Color Trigger --}}
                <button x-on:click="$dispatch('open-modal', { name: 'specs-modal', id: {{ $product_id }} })" 
                        class="w-12 h-12 border-2 border-dashed border-black/20 flex items-center justify-center hover:border-black transition-colors">
                    <flux:icon.plus size="sm" />
                </button>
            </div>
        </div>

        {{-- 2. Dual Uploader (Only shows if color is selected) --}}
        @if($selected_variant_id)
            <div class="grid grid-cols-2 gap-10 animate-fade-in" 
                 x-data="{ front: @entangle('front_path'), back: @entangle('back_path') }"
                 x-init="window.initProductMediaPond($data, $refs.front, 'front'); window.initProductMediaPond($data, $refs.back, 'back')"
                 x-on:reset-ponds.window="/* FilePond handles its own reset via entangle */">
                
                <div class="space-y-4">
                    <flux:label class="uppercase tracking-widest text-[10px] font-black">Front View</flux:label>
                    <div wire:ignore class="gothic-pond-wrapper border-2 border-black dark:border-white">
                        <input type="file" x-ref="front">
                    </div>
                </div>

                <div class="space-y-4">
                    <flux:label class="uppercase tracking-widest text-[10px] font-black">Reverse View</flux:label>
                    <div wire:ignore class="gothic-pond-wrapper border-2 border-black dark:border-white">
                        <input type="file" x-ref="back">
                    </div>
                </div>

                <div class="col-span-2 pt-6">
                    <flux:button wire:click="saveColorVisuals" class="w-full bg-black text-white h-14 uppercase font-black tracking-[0.4em] text-[11px] rounded-none hover:invert">
                        Sync Assets to Colorway
                    </flux:button>
                </div>
            </div>
        @else
            <div class="h-48 flex items-center justify-center italic opacity-20 uppercase text-[10px] tracking-widest border-2 border-dashed border-black/5">
                Select a colorway above to unlock visual matrix
            </div>
        @endif
    @endif
</div>
