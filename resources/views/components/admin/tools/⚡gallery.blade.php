<?php

use Livewire\Component;
use App\Services\Admin\ProductMediaService;
use Livewire\Attributes\On;

new class extends Component {

    public ?int $productId = null;
    public string $media = '';

    // Direct-embed entry point (product edit page tab).
    public function mount($productId = null)
    {
        if ($productId) {
            $this->openForProduct($productId);
        }
    }

    #[On('media-gallery-modal')]
    public function openForProduct($id = null)
    {
        $this->reset(['media', 'productId']);
        $this->productId = $id;
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
            <flux:label>Add media (images, video, PDF)</flux:label>
            <input type="file" id="post-uploader" multiple>
        </div>

        {{-- SUBMIT --}}
        <div>
            <flux:button type="submit" id="submitBtn" variant="primary" class="w-full">
                Upload
            </flux:button>
        </div>

    </form>

</div>