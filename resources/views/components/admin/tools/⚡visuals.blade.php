<?php

use Livewire\Component;
use Livewire\Attributes\On;
use App\Services\Admin\ProductContextService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

new class extends Component {

    public $product_id;

    public $active_color = '';
    public $active_hex = '';

    // Bare temp/ basenames handed back by /admin/upload/complete — not file
    // objects. The chunked Uppy transport owns the actual bytes; these are
    // just pointers into storage/app/public/temp until saveImages() promotes
    // them, same "assemble to temp, promote on save" shape ProductMediaService
    // already uses for the product-level gallery.
    public $front_path = '';
    public $back_path = '';

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
            'front_path'   => 'nullable|string',
            'back_path'    => 'nullable|string',
        ]);

        $frontPath = $this->front_path ? $this->promote($this->front_path, 'front') : null;
        $backPath  = $this->back_path ? $this->promote($this->back_path, 'back') : null;

        $service->attachUploadedColorImages(
            $this->product_id,
            $this->active_color,
            $this->active_hex,
            $frontPath,
            $backPath
        );

        $this->dispatch('notify', message: 'ASSETS SYNCED');

        $this->reset(['front_path', 'back_path']);
        $this->dispatch('reset-ponds');
    }

    // Moves an assembled chunk-upload result out of temp/ into a stable,
    // product-scoped path. Assembly (ChunkUploadController::complete) already
    // validated size and hands back a bare basename inside temp/.
    protected function promote(string $tempBasename, string $side): string
    {
        $source = "temp/{$tempBasename}";

        if (!Storage::disk('public')->exists($source)) {
            throw new \Exception("Uploaded {$side} asset is missing — please re-upload.");
        }

        $extension = pathinfo($tempBasename, PATHINFO_EXTENSION);
        $final = "media/colors/{$this->product_id}/" . Str::slug($this->active_color) . "-{$side}-" . Str::uuid() . ".{$extension}";

        Storage::disk('public')->move($source, $final);

        return $final;
    }

    // Direct-embed entry point (product edit page tab) — the event listener
    // below stays for anything still dispatching the old modal-trigger event.
    public function mount($productId = null)
    {
        if ($productId) {
            $this->init($productId);
        }
    }

    #[On('load-visuals-tool')]
    public function init($id)
    {
        $this->product_id = $id;

        $this->reset([
            'active_color',
            'active_hex',
            'front_path',
            'back_path',
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


<div class="space-y-10">

    {{-- HEADER --}}
    <header class="flex flex-col gap-4">
        <div class="flex items-center gap-3">
            <p class="text-sm text-zinc-500">
                Colorway: {{ $active_color ?: 'none selected' }}
            </p>
            @if($active_hex)
                <div class="w-4 h-4 rounded border border-zinc-300 dark:border-zinc-700"
                     style="background-color: {{ $active_hex }}"></div>
            @endif
        </div>

        @if($active_color && ($front_path || $back_path))
            <flux:button wire:click="saveImages" wire:loading.attr="disabled" variant="primary">
                <span wire:loading.remove>Save Images</span>
                <span wire:loading>Saving…</span>
            </flux:button>
        @endif
    </header>

    {{-- BODY --}}
    <div class="flex flex-col gap-10">

        {{-- COLOR SELECTION --}}
        <section class="flex flex-col gap-4">
            <flux:label>Colorway</flux:label>

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
                        class="flex items-center gap-2 text-sm  border px-3 py-2 transition-colors
                        {{ $active_color === $name
                            ? 'bg-zinc-900 text-white border-zinc-900 dark:bg-white dark:text-zinc-900 dark:border-white'
                            : 'border-black/15 dark:border-white/15 hover:border-black/40 dark:hover:border-white/40' }}">

                        <span class="w-3 h-3 rounded-full border border-black/10" style="background-color: {{ $hex }}"></span>
                        {{ $name }}
                    </button>
                @endforeach
            </div>
        </section>

        {{-- EXISTING COLORWAYS --}}
        <section class="flex flex-col gap-3 border-t border-black/15 dark:border-white/15 pt-6">
            <flux:label>Existing Colorways</flux:label>

            <div class="flex flex-col gap-2">
                @forelse($colors as $color)
                    <div class="flex items-center gap-3">
                        <div class="w-5 h-5 rounded border border-zinc-300 dark:border-zinc-700"
                             style="background-color: {{ $color->hex_code }}">
                        </div>
                        <span class="text-sm">{{ $color->color_name }}</span>
                    </div>
                @empty
                    <p class="text-sm text-zinc-500">No colorways yet.</p>
                @endforelse
            </div>
        </section>

        {{-- UPLOADERS --}}
        <section class="grid grid-cols-1 sm:grid-cols-2 gap-6 {{ !$active_color ? 'opacity-40 pointer-events-none' : '' }}"
             x-data="{
                frontPct: 0, backPct: 0,
                frontErr: '', backErr: '',
                frontPond: null, backPond: null,
             }"
             x-on:reset-ponds.window="frontPct = 0; backPct = 0; frontErr = ''; backErr = '';">

{{-- FRONT --}}
<div class="flex flex-col gap-3">
    <div class="flex items-center gap-2">
        <flux:label>Front</flux:label>
        @if($front_path)
            <flux:icon.check-circle variant="micro" class="text-emerald-500" />
        @endif
    </div>

    <div class=" border border-dashed border-zinc-300 dark:border-zinc-700 hover:border-zinc-400 dark:hover:border-zinc-600 p-6 text-center cursor-pointer transition-colors"
         x-on:click="frontPond.browse()">
        <input type="file" accept="image/*" class="hidden" wire:ignore
            x-init="frontPond = window.initColorImagePond($el, {
                onStart() { frontPct = 1; frontErr = ''; },
                onProgress(p) { frontPct = p; },
                onComplete(name) { frontPct = 100; $wire.set('front_path', name); },
                onError() { frontPct = 0; frontErr = 'Upload failed — try again.'; },
            })">
        <span class="text-sm text-zinc-500" x-show="frontPct === 0">Click to upload</span>
        <span class="text-sm text-zinc-500" x-show="frontPct > 0 && frontPct < 100" x-text="'Uploading ' + frontPct + '%'"></span>
        <span class="text-sm text-emerald-600 dark:text-emerald-400" x-show="frontPct === 100">Uploaded</span>
        <p class="text-xs text-red-500 mt-2" x-show="frontErr" x-text="frontErr"></p>
    </div>
</div>

{{-- BACK --}}
<div class="flex flex-col gap-3">
    <div class="flex items-center gap-2">
        <flux:label>Back</flux:label>
        @if($back_path)
            <flux:icon.check-circle variant="micro" class="text-emerald-500" />
        @endif
    </div>

    <div class=" border border-dashed border-zinc-300 dark:border-zinc-700 hover:border-zinc-400 dark:hover:border-zinc-600 p-6 text-center cursor-pointer transition-colors"
         x-on:click="backPond.browse()">
        <input type="file" accept="image/*" class="hidden" wire:ignore
            x-init="backPond = window.initColorImagePond($el, {
                onStart() { backPct = 1; backErr = ''; },
                onProgress(p) { backPct = p; },
                onComplete(name) { backPct = 100; $wire.set('back_path', name); },
                onError() { backPct = 0; backErr = 'Upload failed — try again.'; },
            })">
        <span class="text-sm text-zinc-500" x-show="backPct === 0">Click to upload</span>
        <span class="text-sm text-zinc-500" x-show="backPct > 0 && backPct < 100" x-text="'Uploading ' + backPct + '%'"></span>
        <span class="text-sm text-emerald-600 dark:text-emerald-400" x-show="backPct === 100">Uploaded</span>
        <p class="text-xs text-red-500 mt-2" x-show="backErr" x-text="backErr"></p>
    </div>
</div>


        </section>

        {{-- EMPTY STATE --}}
        @if(!$active_color)
            <div class="p-6  border border-dashed border-black/15 dark:border-white/15 text-center">
                <p class="text-sm text-zinc-500">
                    Select a colorway above to upload images.
                </p>
            </div>
        @endif

    </div>
</div>