<?php

use Livewire\Component;
use App\Services\Admin\ProductMediaService; 
use Livewire\Attributes\On;
use Livewire\Flux\Flux;

new class extends Component {

    public ?int $productId = null;
    public string $media = '';

    #[On('media-gallery-modal')]
    public function openForProduct($id = null)
    {
        $this->reset(['media', 'productId']);
        $this->productId = $id;

        $this->js('$flux.modal("media-upload-modal").show()');
    }

    protected function rules()
    {
        return [
            'media' => 'required|string',
        ];
    }

    public function store()
    {
        $this->validate();

        $productId = $this->productId;
        $media     = $this->media;

        app(ProductMediaService::class)->handle([
            'product_id' => $productId,
            'media'      => $media,
        ]);

        $this->reset(['media', 'productId']);

        $this->dispatch('resetPostMediaPond');
        //\Flux::modals()->close("media-upload-modal");
        $this->dispatch('media-added');
    }
};

?>

<div 
    x-data="{ media: @entangle('media') }"
    x-init="$nextTick(() => window.initPostMediaPond($el))"
>

    @if (session()->has('success'))
        <div class="mb-4 p-3 bg-green-50 border border-green-300 text-green-800 rounded">
            {{ session('success') }}
        </div>
    @endif

    <form wire:submit.prevent="store">

        {{-- FILEPOND --}}
        <div wire:ignore class="mb-4">
            <flux:label class="uppercase tracking-[0.4em] text-[11px] font-black italic">
                Creator Library (Drop Content Here)
            </flux:label>

            <input type="file" id="post-uploader" multiple>
        </div>

        {{-- SUBMIT --}}
        <div class="space-y-4">
            <flux:button 
                type="submit" 
                id="submitBtn" 
                variant="filled" 
                color="primary" 
                class="w-full"
            >
                Publish Package
            </flux:button>
        </div>

    </form>

    {{-- DEBUG --}}
    <div class="mt-8 p-4 bg-gray-50 border rounded text-xs overflow-auto max-h-60">
        <h3 class="font-bold mb-2 uppercase tracking-widest text-[10px]">
            Active Media Records:
        </h3>
        {{ App\Models\Media::all() }}
    </div>

</div>