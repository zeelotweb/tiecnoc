<?php

use Livewire\Component;
use App\Models\Product;
use App\Models\ProductColor;

new class extends Component
{
    public ?int $productId = null;
    public array $colors = [];

    protected $listeners = ['load-color-toggle-tool' => 'loadProduct'];

    /*
    |--------------------------------------------------------------------------
    | LOAD PRODUCT (EVENT SAFE)
    |--------------------------------------------------------------------------
    */
    public function loadProduct($payload = null)
    {
        $id = is_array($payload) ? ($payload['id'] ?? null) : $payload;

        if (! $id) return;

        $this->productId = $id;

        $product = Product::with('colors.variants')->findOrFail($id);

        $this->colors = $product->colors->map(function ($color) {

            $hasImage = !empty($color->front_image_path);
            $hasVariants = $color->variants->count() > 0;

            return [
                'id' => $color->id,
                'name' => $color->color_name,
                'status' => $color->status ?? 'draft',
                'ready' => $hasImage && $hasVariants,
                'missing' => [
                    'image' => !$hasImage,
                    'variants' => !$hasVariants,
                ],
            ];
        })->toArray();
    }

    /*
    |--------------------------------------------------------------------------
    | TOGGLE COLOR
    |--------------------------------------------------------------------------
    */
    public function toggleColor($colorId)
    {
        $color = ProductColor::with('variants')->find($colorId);

        if (! $color) {
            $this->dispatch('notify', message: 'COLOR NOT FOUND', type: 'error');
            return;
        }

        $hasImage = !empty($color->front_image_path);
        $hasVariants = $color->variants->count() > 0;

        if (! $hasImage || ! $hasVariants) {
            $this->dispatch('notify', message: 'INCOMPLETE COLOR SETUP', type: 'error');
            return;
        }

        $color->status = $color->status === 'live' ? 'draft' : 'live';
        $color->save();

        $this->loadProduct(['id' => $this->productId]);
    }
};
?>




<div>

@if(empty($colors))
    <div class="text-[10px] uppercase opacity-50">
        Loading color matrix...
    </div>
@else

@foreach($colors as $color)

    <div class="flex justify-between items-center border-b py-2">

        <div>
            <div class="font-bold uppercase">
                {{ $color['name'] }}
            </div>

            @if(!$color['ready'])
                <div class="text-[10px] text-red-500 uppercase space-x-2">
                    @if($color['missing']['image']) <span>Image</span> @endif
                    @if($color['missing']['variants']) <span>Specs</span> @endif
                    <span>Required</span>
                </div>
            @endif
        </div>

        <button 
            wire:click="toggleColor({{ $color['id'] }})"
            wire:loading.attr="disabled"
            @disabled(!$color['ready'])
            class="px-2 py-1 border {{ $color['status'] === 'live' ? 'bg-green-500 text-white' : '' }}"
        >
            {{ $color['status'] === 'live' ? 'LIVE' : 'DRAFT' }}
        </button>

    </div>

@endforeach

@endif

</div>