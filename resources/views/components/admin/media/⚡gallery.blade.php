<?php

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\Product;
use App\Models\Media;

new class extends Component {

    public ?int $product_id = null;
    public ?Product $product = null;

    // Direct-embed entry point (product edit page tab).
    public function mount($productId = null)
    {
        if ($productId) {
            $this->loadMediaTool($productId);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | LOAD FROM MODAL
    |--------------------------------------------------------------------------
    */
    #[On('load-media-tool')]
    public function loadMediaTool($id)
    {
        if (!$id) return;

        $this->product_id = (int) $id;
        $this->product = Product::find($this->product_id);
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE MEDIA
    |--------------------------------------------------------------------------
    */
    public function delete($id)
    {
        Media::where('id', $id)
            ->where('mediable_type', Product::class)
            ->delete();

        $this->dispatch('notify', message: 'MEDIA REMOVED');
        $this->dispatch('$refresh');
    }

    /*
    |--------------------------------------------------------------------------
    | DATA
    |--------------------------------------------------------------------------
    */
    public function with()
    {
        return [
            'gallery' => $this->product_id
                ? Media::query()
                    ->where('mediable_type', 'product')
                    ->where('mediable_id', $this->product_id)
                    ->where('collection', 'product.misc')
                    ->latest()
                    ->get()
                : collect(),
        ];
    }
};
?>


<div class="space-y-4">

    <flux:label>Media Library</flux:label>

    {{-- GRID --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">

        @forelse($gallery as $media)

            @php
                $path = $media->path ?? null;
                $url  = $path ? asset('storage/' . $path) : null;
                $type = $media->type ?? null;
                $ext  = strtolower(pathinfo($path ?? '', PATHINFO_EXTENSION));
            @endphp

            <div class=" border border-black/15 dark:border-white/15 overflow-hidden relative group">

                {{-- DELETE --}}
                <button
                    wire:click="delete({{ $media->id }})"
                    class="absolute top-1.5 right-1.5 w-6 h-6 rounded-full bg-black/60 text-white text-xs opacity-0 group-hover:opacity-100 transition-opacity z-10 flex items-center justify-center">
                    ✕
                </button>

                {{-- IMAGE --}}
                @if($url && ($type && str_starts_with($type, 'image') || in_array($ext, ['jpg','jpeg','png','webp','gif'])))
                    <img src="{{ $url }}" class="w-full h-32 object-cover" loading="lazy" />

                {{-- VIDEO --}}
                @elseif($url && ($type && str_starts_with($type, 'video') || in_array($ext, ['mp4','mov','webm'])))
                    <video controls class="w-full h-32 object-cover">
                        <source src="{{ $url }}" type="{{ $type ?: 'video/mp4' }}">
                    </video>

                {{-- PDF --}}
                @elseif($url && ($type === 'application/pdf' || $ext === 'pdf'))
                    <iframe src="{{ $url }}" class="w-full h-32"></iframe>

                {{-- FALLBACK --}}
                @else
                    <div class="flex items-center justify-center h-32 text-xs text-zinc-500">
                        Unsupported file
                    </div>
                @endif

            </div>

        @empty

            <div class="col-span-full text-center py-10 text-sm text-zinc-500">
                No media yet.
            </div>

        @endforelse

    </div>

</div>