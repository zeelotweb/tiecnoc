<?php

use Livewire\Component;

use App\Models\Product;
use App\Models\ProductVariant;

new class extends Component {
    public Product $product;
    
    // Form state for a new variant
    public $sku = '';
    public $color = '';
    public $size = '';
    public $price = '';
    public $stock_quantity = 0;

    public function addVariant()
    {
        $this->validate([
            'sku' => 'required|unique:product_variants,sku',
            'color' => 'required',
            'size' => 'required',
            'price' => 'required|numeric',
            'stock_quantity' => 'required|integer',
        ]);

        $this->product->variants()->create([
            'sku' => $this->sku,
            'color' => $this->color,
            'size' => $this->size,
            'price' => $this->price,
            'stock_quantity' => $this->stock_quantity,
        ]);

        $this->reset(['sku', 'color', 'size', 'price', 'stock_quantity']);
        $this->dispatch('variant-added'); // To refresh any lists or notify the UI
    }

    public function deleteVariant($id)
    {
        ProductVariant::find($id)->delete();
    }
}; ?>

<div class="space-y-6">
    <flux:card>
        <flux:heading size="lg">Inventory & SKUs</flux:heading>
        <flux:subheading>Add the specific sizes and colors for this item.</flux:subheading>

        <form wire:submit="addVariant" class="mt-6 grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
            <flux:input wire:model="sku" label="SKU" placeholder="TNC-POLO-BLK-M" />
            
            <flux:select wire:model="color" label="Color" placeholder="Pick color">
                <flux:select.item value="Desert Sky">Desert Sky</flux:select.item>
                <flux:select.item value="Optic White">Optic White</flux:select.item>
                <flux:select.item value="Black">Black</flux:select.item>
            </flux:select>

            <flux:select wire:model="size" label="Size" placeholder="Pick size">
                <flux:select.item value="XS">XS</flux:select.item>
                <flux:select.item value="S">S</flux:select.item>
                <flux:select.item value="M">M</flux:select.item>
                <flux:select.item value="L">L</flux:select.item>
                <flux:select.item value="XL">XL</flux:select.item>
            </flux:select>

            <flux:input wire:model="price" label="Price" icon="currency-dollar" placeholder="0.00" />
            <flux:input type="number" wire:model="stock_quantity" label="Qty" placeholder="0" />
            
            <div class="md:col-span-5 flex justify-end">
                <flux:button type="submit" variant="filled" size="sm" icon="plus">Add SKU</flux:button>
            </div>
        </form>

        <hr class="my-8 border-zinc-200 dark:border-zinc-800" />

        {{-- The Inventory Table --}}
        <flux:table>
            <flux:table.columns>
                <flux:table.column>SKU</flux:table.column>
                <flux:table.column>Color/Size</flux:table.column>
                <flux:table.column>Price</flux:table.column>
                <flux:table.column>Stock</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach($product->variants as $variant)
                    <flux:table.row :key="$variant->id">
                        <flux:table.cell class="font-medium text-zinc-900">{{ $variant->sku }}</flux:table.cell>
                        <flux:table.cell>{{ $variant->color }} / {{ $variant->size }}</flux:table.cell>
                        <flux:table.cell>${{ number_format($variant->price, 2) }}</flux:table.cell>
                        <flux:table.cell>
                             <flux:badge color="{{ $variant->stock_quantity < 5 ? 'red' : 'zinc' }}" inset="none">
                                {{ $variant->stock_quantity }} in stock
                             </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:button wire:click="deleteVariant({{ $variant->id }} )" variant="ghost" icon="trash" size="sm" />
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    </flux:card>
</div>
