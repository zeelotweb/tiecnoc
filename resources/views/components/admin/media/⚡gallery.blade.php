<?php

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\Product;
use App\Models\Media;

new class extends Component {

    public ?int $product_id = null;
    public ?Product $product = null;

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


<div class="p-6 space-y-6">

    {{-- HEADER --}}
    <div class="border-b pb-4">
        <flux:heading size="lg" class="uppercase font-black italic">
            {{ $product?->name ?? 'MEDIA_GALLERY' }}
        </flux:heading>

        <p class="text-[10px] uppercase tracking-[0.3em] opacity-40">
            Product ID: {{ $product_id ?? 'NULL' }}
        </p>
    </div>

    {{-- GRID --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">

        @forelse($gallery as $media)

            @php
                $path = $media->path ?? null;
                $url  = $path ? asset('storage/' . $path) : null;
                $type = $media->type ?? null;
                $ext  = strtolower(pathinfo($path ?? '', PATHINFO_EXTENSION));
            @endphp

            <div class="border p-2 relative group flex flex-col gap-2">

                {{-- DELETE --}}
                <button 
                    wire:click="delete({{ $media->id }})"
                    class="absolute top-1 right-1 text-xs opacity-0 group-hover:opacity-100 z-10">
                    ✕
                </button>

                {{-- IMAGE --}}
                @if($url && ($type && str_starts_with($type, 'image') || in_array($ext, ['jpg','jpeg','png','webp','gif'])))
                    <img src="{{ $url }}" class="w-full h-40 object-cover" loading="lazy" />

                {{-- VIDEO --}}
                @elseif($url && ($type && str_starts_with($type, 'video') || in_array($ext, ['mp4','mov','webm'])))
                    <video controls class="w-full h-40 object-cover">
                        <source src="{{ $url }}" type="{{ $type ?: 'video/mp4' }}">
                    </video>

                {{-- PDF --}}
                @elseif($url && ($type === 'application/pdf' || $ext === 'pdf'))
                    <iframe src="{{ $url }}" class="w-full h-40"></iframe>

                {{-- FALLBACK --}}
                @else
                    <div class="flex items-center justify-center h-40 text-[10px] uppercase opacity-50">
                        Unsupported / Missing
                    </div>
                @endif

                {{-- META DEBUG --}}
                <div class="text-[8px] opacity-40 break-all leading-tight">
                    <div>ID: {{ $media->id }}</div>
                    <div>TYPE: {{ $type ?? 'NULL' }}</div>
                    <div>EXT: {{ $ext ?: 'NULL' }}</div>
                    <div>PATH: {{ $path ?? 'NULL' }}</div>
                </div>

            </div>

        @empty

            <div class="col-span-full text-center py-10 text-xs uppercase opacity-40 italic">
                No Media Found
            </div>

        @endforelse

    </div>

</div>