<?php

use Livewire\Component;
use App\Models\Product;
use App\Services\Store\CartService;
use App\Services\Store\ActivityService;

new class extends Component {

    public ?Product $product = null;

    public $selectedColorId = null;
    public $selectedVariantId = null;

    public $state = 'idle'; // idle | loading | ready | added

    protected $listeners = ['quick-add' => 'loadProduct'];

    /**
     * Load product from trigger
     */
    public function loadProduct($id)
    {
        $this->resetState();

        if (!$id) {
            return;
        }

        $this->state = 'loading';

        $this->product = Product::with(['colors.variants'])->find($id);

        if ($this->product) {
            $this->selectedColorId = $this->product->colors->first()?->id;
            $this->selectedVariantId = null;

            $this->state = 'ready';
        }
    }

    protected function resetState()
    {
        $this->reset([
            'product',
            'selectedColorId',
            'selectedVariantId'
        ]);

        $this->state = 'idle';
    }

    public function selectColor($colorId)
    {
        $this->selectedColorId = $colorId;
        $this->selectedVariantId = null;
    }

    public function selectSize($variantId)
    {
        $this->selectedVariantId = $variantId;
    }

    public function addToCart(CartService $cart, ActivityService $activity)
    {
        if (!$this->product) return;

        if ($activity->owns($this->product)) {
            $this->dispatch('notify', message: 'PIECE ALREADY OWNED', type: 'error');
            return;
        }

        if (!$this->selectedVariantId) {
            $this->dispatch('notify', message: 'SELECT SIZE', type: 'error');
            return;
        }

        try {
            $cart->add($this->selectedVariantId);

            $this->dispatch('notify', message: 'ADDED TO CART', type: 'success');
            $this->dispatch('cart-updated');

            $this->state = 'added';

            $this->dispatch('close-modal', name: 'quick-add-modal');

        } catch (\Exception $e) {
            $this->dispatch('notify', message: 'SYSTEM ERROR', type: 'error');
        }
    }

    public function getActiveColorProperty()
    {
        return $this->product?->colors
            ->firstWhere('id', $this->selectedColorId);
    }

    public function getActiveVariantsProperty()
    {
        return $this->activeColor?->variants ?? collect();
    }

    public function with(ActivityService $activity)
    {
        return [
            'isPaid' => $this->product
                ? $activity->owns($this->product)
                : false,
        ];
    }
};
?>


<div class="p-6">

    {{-- LOADING STATE --}}
    @if($state === 'loading')
        <div class="p-12 text-center opacity-40 italic text-xs">
            Loading Product...
        </div>

    {{-- ADDED STATE --}}
    @elseif($state === 'added')
        <div class="p-12 text-center space-y-3">
            <div class="text-xs font-black italic tracking-widest text-[#E31837]">
                ADDED TO CART
            </div>
            <div class="text-[10px] opacity-40 italic">
                Closing...
            </div>
        </div>

    {{-- IDLE EMPTY STATE --}}
    @elseif($state === 'idle')
        <div class="p-12 text-center opacity-40 italic text-xs">
            Awaiting Product Selection...
        </div>

    {{-- READY STATE --}}
    @elseif($product)
        <div class="space-y-8" wire:key="qv-{{ $product->id }}">

            {{-- COLORS --}}
            <div class="space-y-6">
                <label class="uppercase text-[10px] font-black tracking-widest italic">
                    Colorway
                </label>

                <div class="flex flex-wrap gap-4">
                    @foreach($product->colors as $c)
                        <button wire:click="selectColor({{ $c->id }})" type="button">
                            <div class="w-14 h-14 border-2 
                                {{ $selectedColorId == $c->id 
                                    ? 'border-[#E31837] scale-110' 
                                    : 'border-black dark:border-white opacity-60' }} p-1">

                                <div class="w-full h-full border border-black/10"
                                     style="background-color: {{ $c->hex_code }}"></div>
                            </div>
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- SIZES --}}
            <div class="space-y-6 pt-4 border-t border-black dark:border-white/10">
                <label class="uppercase text-[10px] font-black tracking-widest italic">
                    Sizes
                </label>

                <div class="flex flex-wrap gap-3">
                    @forelse($this->activeVariants as $v)
                        <button wire:click="selectSize({{ $v->id }})"
                            class="min-w-[60px] h-14 border-2 flex items-center justify-center px-6
                            {{ $selectedVariantId == $v->id 
                                ? 'bg-[#E31837] border-[#E31837] text-white' 
                                : 'border-black dark:border-white hover:bg-black hover:text-white dark:hover:bg-white dark:hover:text-black' }}">

                            <span class="text-xs font-black uppercase italic">
                                {{ $v->size }}
                            </span>
                        </button>
                    @empty
                        <p class="text-[10px] opacity-30 italic uppercase font-bold">
                            No Sizes
                        </p>
                    @endforelse
                </div>
            </div>

            {{-- CTA --}}
            <div class="pt-6">
                @if($isPaid)
                    <div class="w-full py-8 border-4 border-[#E31837] text-center italic font-black">
                        OWNED
                    </div>
                @else
                    <button 
                        wire:click="addToCart"
                        class="w-full bg-black text-white dark:bg-white dark:text-black py-8 font-black uppercase italic tracking-widest hover:bg-[#E31837]"
                        {{ !$selectedVariantId ? 'disabled' : '' }}>

                        {{ $selectedVariantId ? 'Add To Cart' : 'Select Size' }}
                    </button>
                @endif
            </div>

        </div>
    @endif

</div>