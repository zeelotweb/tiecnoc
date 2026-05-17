<?php

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\On;
use App\Services\Admin\ProductContextService;

new class extends Component {

    use WithFileUploads;

    public $product_id;

    public $active_color = '';
    public $active_hex = '';

    public $front_file;
    public $back_file;

    public function selectActiveColor($name, $hex)
    {
        $this->active_color = $name;
        $this->active_hex   = $hex;
    }

    public function saveImages(ProductContextService $service)
    {
        $this->validate([
            'product_id'   => 'required|exists:products,id',
            'active_color' => 'required|string',
            'front_file'   => 'nullable|image|max:10240',
            'back_file'    => 'nullable|image|max:10240',
        ]);

        $frontPath = $this->front_file
            ? $this->front_file->store("merch/tmp", 'public')
            : null;

        $backPath = $this->back_file
            ? $this->back_file->store("merch/tmp", 'public')
            : null;

        $service->attachUploadedColorImages(
            $this->product_id,
            $this->active_color,
            $this->active_hex,
            $frontPath,
            $backPath
        );

        $this->dispatch('notify', message: 'ASSETS SYNCED');

        $this->reset(['front_file', 'back_file']);
        $this->dispatch('reset-ponds');
    }

    #[On('load-visuals-tool')]
    public function init($id)
    {
        $this->product_id = $id;

        $this->reset([
            'active_color',
            'active_hex',
            'front_file',
            'back_file'
        ]);

        $this->dispatch('reset-ponds');
    }

    public function with()
    {
        return [
            'product' => $this->product_id
                ? \App\Models\Product::find($this->product_id)
                : null,

            'colors' => $this->product_id
                ? \App\Models\ProductColor::where('product_id', $this->product_id)->get()
                : collect(),
        ];
    }
};

?>


<div class="w-full overflow-hidden p-6 space-y-12 bg-white dark:bg-black text-black dark:text-white border border-black/10">

    {{-- HEADER --}}
    <header class="flex flex-col border-b-2 border-black dark:border-white pb-8 gap-6">
        
        <div>
            <flux:heading size="xl" class="uppercase tracking-tighter font-black italic break-words leading-none">
                {{ $product->name ?? 'STUDIO_ASSET_MANAGER' }}
            </flux:heading>

            <div class="flex flex-col gap-2 mt-4">
                <p class="text-[10px] uppercase tracking-[0.4em] opacity-40 italic">
                    Mapping / {{ $active_color ?: 'AWAITING_SELECTION' }}
                </p>

                @if($active_hex)
                    <div class="w-4 h-4 border border-black/20"
                         style="background-color: {{ $active_hex }}"></div>
                @endif
            </div>
        </div>

        @if($active_color && ($front_file || $back_file))
            <flux:button 
                wire:click="saveImages" 
                wire:loading.attr="disabled"
                class="w-full bg-black text-white dark:bg-white dark:text-black rounded-none px-6 h-14 uppercase tracking-[0.4em] text-[11px] font-black hover:invert transition-all border-none">
                
                <span wire:loading.remove>Commit to Storage</span>
                <span wire:loading>Syncing...</span>

            </flux:button>
        @endif
    </header>

    {{-- BODY --}}
    <div class="flex flex-col gap-12">

        {{-- COLOR SELECTION --}}
        <section class="flex flex-col gap-6">
            <flux:label class="uppercase text-[11px] font-black tracking-[0.2em] border-l-4 border-black dark:border-white pl-4 italic">
                01 / Metadata Identity
            </flux:label>

            <div class="flex flex-wrap gap-2">
                @php
                    $palette = [
                        'Black' => '#000000',
                        'Optic White' => '#FFFFFF',
                        'Desert Sky' => '#002451',
                        'Heather Grey' => '#9CA3AF',
                        'Crimson' => '#B91C1C',
                        'Electric' => '#EAB308',
                        'Blush' => '#F472B6',
                        'Forest' => '#14532D',
                        'Cobalt' => '#1D4ED8',
                    ];
                @endphp

                @foreach($palette as $name => $hex)
                    <button 
                        type="button"
                        wire:click="selectActiveColor('{{ $name }}', '{{ $hex }}')" 
                        class="flex items-center gap-3 text-[10px] uppercase border-2 px-5 py-3 transition-all
                        {{ $active_color === $name 
                            ? 'bg-black text-white border-black dark:bg-white dark:text-black'
                            : 'border-black/10 opacity-70' }}">

                        <span class="w-3 h-3" style="background-color: {{ $hex }}"></span>
                        {{ $name }}
                    </button>
                @endforeach
            </div>
        </section>

        {{-- ASSET MATRIX (NOW COLORS, NOT VARIANTS) --}}
        <section class="flex flex-col gap-6 border-t border-black/10 pt-8">
            <flux:label class="uppercase text-[10px] font-bold tracking-[0.3em] opacity-30 italic">
                04 / Asset_Matrix
            </flux:label>

            <div class="flex flex-col gap-2">
                @forelse($colors as $color)
                    <div class="flex items-center gap-3">
                        <div 
                            class="w-6 h-6 border"
                            style="background-color: {{ $color->hex_code }}">
                        </div>
                        <span class="text-[10px] uppercase font-black">
                            {{ $color->name }}
                        </span>
                    </div>
                @empty
                    <p class="text-[10px] uppercase opacity-20 italic">
                        Inventory_Null
                    </p>
                @endforelse
            </div>
        </section>

        {{-- UPLOADERS --}}
        <section class="flex flex-col gap-10 {{ !$active_color ? 'opacity-20 pointer-events-none' : '' }}">
{{-- FRONT --}}
<div class="flex flex-col gap-4">
    <flux:label class="uppercase tracking-[0.4em] text-[11px] font-black italic">
        02 / Front_Asset
    </flux:label>

    @if($front_file)
        <flux:icon.check size="sm" class="text-green-500" />
    @endif

    <div wire:ignore class="gothic-pond-wrapper">
        <input type="file" x-init="
            FilePond.create($el, { 
                labelIdle: '<span class=\'text-[10px] uppercase tracking-[0.3em] font-bold\'>Upload_Rendering</span>',
                server: { process: (n, f, m, l, e, p) => { @this.upload('front_file', f, l, e, p); } } 
            });
            {{-- ⚡ Listen for the reset event and purge the files --}}
            window.addEventListener('reset-ponds', () => {
                const pond = FilePond.find($el);
                if (pond) pond.removeFiles();
            });
        ">
    </div>
</div>

{{-- BACK --}}
<div class="flex flex-col gap-4">
    <flux:label class="uppercase tracking-[0.4em] text-[11px] font-black italic">
        03 / Reverse_Asset
    </flux:label>

    @if($back_file)
        <flux:icon.check size="sm" class="text-green-500" />
    @endif

    <div wire:ignore class="gothic-pond-wrapper">
        <input type="file" x-init="
            FilePond.create($el, { 
                labelIdle: '<span class=\'text-[10px] uppercase tracking-[0.3em] font-bold\'>Upload_Rendering</span>',
                server: { process: (n, f, m, l, e, p) => { @this.upload('back_file', f, l, e, p); } } 
            });
            {{-- ⚡ Listen for the reset event and purge the files --}}
            window.addEventListener('reset-ponds', () => {
                const pond = FilePond.find($el);
                if (pond) pond.removeFiles();
            });
        ">
    </div>
</div>


        </section>

        {{-- EMPTY STATE --}}
        @if(!$active_color)
            <div class="p-6 border-2 border-dashed border-black/5 dark:border-white/5 text-center">
                <p class="text-[11px] uppercase tracking-[0.5em] opacity-30 italic font-black">
                    Select Metadata Context To Unlock Uploaders
                </p>
            </div>
        @endif

    </div>
</div>