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
    // Direct-embed entry point (product edit page tab).
    public function mount($productId = null)
    {
        if ($productId) {
            $this->hydrateEditor($productId);
        }
    }

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
        $this->dispatch('notify', message: 'REGISTRY IDENTITY UPDATED', type: 'success');
        $this->dispatch('matrix-updated');
    }

    public function discard()
    {
        $this->hydrateEditor($this->model_id);
    }
}; 
?>

<div>
    @if($model_id)
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
            <div class="pt-6 border-t border-black/15 dark:border-white/15 flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <flux:label>Status</flux:label>
                    <flux:select wire:model="status" class="w-32">
                        <flux:select.option value="draft">Draft</flux:select.option>
                        <flux:select.option value="live">Live</flux:select.option>
                    </flux:select>
                </div>

                <div class="flex gap-3">
                    <flux:button variant="ghost" wire:click="discard">Discard</flux:button>
                    <flux:button type="submit" variant="primary">
                        Save Changes
                    </flux:button>
                </div>
            </div>
        </form>
    @else
        {{-- Loading State --}}
        <div class="h-48 flex items-center justify-center text-sm text-zinc-500">
            Select a product to edit.
        </div>
    @endif
</div>




