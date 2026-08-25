<?php

use Livewire\Component;
use App\Services\Store\ReactionService;

new class extends Component {

    public $variantId;
    public $type = 'favorite';
    public $active = false;

    public function mount(ReactionService $reactions, $variantId, $type = 'favorite')
    {
        $this->variantId = $variantId;
        $this->type = $type;

        $this->active = $reactions->exists($this->variantId, $this->type);
    }

    public function toggle(ReactionService $reactions)
    {
        if (!$this->variantId) return;

        $this->active = $reactions->toggle($this->variantId, $this->type);

        $this->dispatch('notify', 
            message: $this->active ? strtoupper($this->type).' ADDED' : strtoupper($this->type).' REMOVED',
            type: 'success'
        );
    }
};
?>

<button
    type="button"
    wire:click.stop="toggle"
    class="relative z-10 flex items-center justify-center cursor-pointer bg-neutral-50 p-2">

    <flux:icon.heart
       variant="{{ $active ? 'solid' : 'outline' }}"
        class="w-5 h-5 transition-all duration-300
        {{ $active
            ? 'text-green-600 scale-110'
            : 'text-black opacity-70 hover:opacity-100' }}"
    />

</button>