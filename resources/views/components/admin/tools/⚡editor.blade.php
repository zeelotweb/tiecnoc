<?php

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\{Product, Category};
use Illuminate\Support\Str;

new class extends Component {
    /**
     * Component State
     */
    public $model_id = null;
    public $name = '';
    public $base_price = '';
    public $compare_at_price = '';
    public $category_id = '';
    public $description = '';
    public $material = '';
    public $fit_type = '';
    public $status = '';

    /**
     * Listener: Hydrates the component when the Matrix trigger is clicked.
     * The parameter names MUST match the keys in the $dispatch object.
     */
    #[On('load-editor-tool')]
    public function hydrateEditor($id)
    {
        $this->model_id = $id;
        
        $product = Product::findOrFail($id);
        
        // Map database values to component properties
        $this->name = $product->name;
        $this->base_price = $product->base_price;
        $this->compare_at_price = $product->compare_at_price;
        $this->category_id = $product->category_id;
        $this->description = $product->description;
        $this->material = $product->material;
        $this->fit_type = $product->fit_type;
        $this->status = $product->status;
    }

    /**
     * Logic: Update the existing Registry record.
     */
    public function updateRegistry()
    {
        $this->validate([
            'name' => 'required|min:3',
            'base_price' => 'required|numeric',
            'category_id' => 'required|exists:categories,id',
        ]);

        $product = Product::findOrFail($this->model_id);
        
        $product->update([
            'name' => $this->name,
            'slug' => Str::slug($this->name),
            'base_price' => (float) $this->base_price,
            'compare_at_price' => $this->compare_at_price ? (float) $this->compare_at_price : null,
            'category_id' => $this->category_id,
            'description' => $this->description,
            'material' => $this->material,
            'fit_type' => $this->fit_type,
            'status' => $this->status,
        ]);

        // Feedback & Global Resets
        $this->dispatch('product-updated'); 
        $this->dispatch('modal-close', name: 'edit-modal');
        $this->dispatch('notify', message: 'REGISTRY IDENTITY UPDATED', type: 'success');
            // ... validation and update logic ...
    $this->dispatch('matrix-updated'); // <--- PING
 
    }
}; 
?>

<div class="p-8 gothic-theme">
    @if($model_id)
        {{-- Header Section --}}
        <flux:header class="flex-col mb-10 border-b border-black dark:border-white pb-6 w-full">
            <flux:heading size="xl" class="flex uppercase italic font-black tracking-tighter w-full">
                04 / Meta Editor
            </flux:heading>
            <flux:subheading class="flex uppercase text-[9px] tracking-[0.4em] opacity-60 w-full">
                Registry Sync / ID: <span class="text-black dark:text-white font-black">{{ $model_id }}</span>
            </flux:subheading>
        </flux:header>

        {{-- Form Section --}}
        <form wire:submit="updateRegistry" class="space-y-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <flux:input wire:model="name" label="Product Name" placeholder="e.g. TNC Signature Hoodie" />
                
                <flux:select wire:model="category_id" label="Registry Category">
                    <flux:select.option value="">Select Category</flux:select.option>
                    @foreach(Category::all() as $cat)
                        <flux:select.option value="{{ $cat->id }}">{{ $cat->name }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>

            <div class="grid grid-cols-2 gap-8">
                <flux:input wire:model="base_price" type="number" step="0.01" label="Retail Price ($)" />
                <flux:input wire:model="compare_at_price" type="number" step="0.01" label="Compare Price ($)" />
            </div>

            <flux:textarea wire:model="description" label="Brand Narrative" rows="4" />

            <div class="grid grid-cols-2 gap-8">
                <flux:input wire:model="material" label="Material Composition" />
                <flux:input wire:model="fit_type" label="Silhouette / Fit" />
            </div>

            {{-- Footer / Actions --}}
            <div class="pt-8 border-t border-black/10 dark:border-white/10 flex justify-between items-center">
                <div class="flex items-center gap-4">
                    <flux:label class="uppercase text-[10px] font-black tracking-widest">Status</flux:label>
                    {{-- Fixed Flux Select syntax --}}
                    <flux:select wire:model="status" class="w-32">
                        <flux:select.option value="draft">STAGING</flux:select.option>
                        <flux:select.option value="live">LIVE</flux:select.option>
                    </flux:select>
                </div>

                <div class="flex gap-3">
                    <flux:button variant="ghost" x-on:click="$dispatch('modal-close', { name: 'edit-modal' })" class="uppercase text-[10px] tracking-widest font-bold">Discard</flux:button>
                    <flux:button type="submit" class="bg-black text-white dark:bg-white dark:text-black px-12 h-14 uppercase tracking-[0.4em] font-black text-[11px] hover:invert rounded-none transition-all">
                        Commit Update
                    </flux:button>
                </div>
            </div>
        </form>
    @else
        {{-- Loading State --}}
        <div class="h-64 flex flex-col items-center justify-center space-y-4 opacity-30 italic uppercase text-[10px] tracking-[0.5em] text-center">
            <flux:spacer />
            <p>Accessing Matrix Registry Data...</p>
            <flux:spacer />
        </div>
    @endif
</div>




