<?php

use Livewire\Component;
use App\Services\Store\CartService;
use Livewire\Attributes\On;

new class extends Component {
    public $count = 0;

    public function mount()
    {
        $this->refreshCount();
    }

    #[On('cart-updated')] 
    public function refreshCount()
    {
        try {
            $this->count = app(CartService::class)->count();
        } catch (\Throwable $e) {
            $this->count = 0; // fail safe
        }
    }

    public function goToCart()
    {
        return $this->redirect(route('store.cart'), navigate: true);
    }
}; ?>
<div wire:click="goToCart"
     class="relative inline-flex items-center group cursor-pointer p-2">
    
    <flux:icon.shopping-bag 
        class="h-5 w-5 group-hover:scale-110 transition-transform duration-300" />
    
    @if($count > 0)
        <span class="absolute top-0 right-0 flex h-4 w-4 items-center justify-center bg-pink-600 text-white text-[8px] font-black leading-none border border-white dark:border-black animate-in zoom-in duration-300">
            {{ $count }}
        </span>
    @endif
</div>