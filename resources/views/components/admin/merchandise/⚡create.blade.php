<?php

use Livewire\Component;
use App\Models\{Product, Category};
use Illuminate\Support\Str;

new class extends Component {

    public $name = '';
    public $gender = 'unisex';
    public $base_price = '';
    public $compare_at_price = '';
    public $category_id = '';
    public $description = '';
    public $material = '';
    public $fit_type = '';

    public $new_category_name = '';

    public function createDraft()
    {
        $this->validate([
            'name'        => 'required|min:3',
            'gender'      => 'required|in:male,female,unisex',
            'base_price'  => 'required|numeric',
            'category_id' => 'required|exists:categories,id',
        ]);

        Product::create([
            'name'             => $this->name,
            'slug'             => Str::slug($this->name),
            'gender'           => $this->gender,
            'base_price'       => (float) $this->base_price,
            'compare_at_price' => $this->compare_at_price ? (float) $this->compare_at_price : null,
            'category_id'      => $this->category_id,
            'description'      => $this->description,
            'material'         => $this->material,
            'fit_type'         => $this->fit_type,
            'status'           => 'draft',
        ]);

        $this->reset();

        $this->dispatch('product-created');
        $this->dispatch('modal-close', name: 'registry-modal');
    }

    public function addCategory()
    {
        $this->validate([
            'new_category_name' => 'required|unique:categories,name',
        ]);

        $category = Category::create([
            'name'      => $this->new_category_name,
            'slug'      => Str::slug($this->new_category_name),
            'is_active' => true,
        ]);

        $this->category_id = $category->id;
        $this->new_category_name = '';

        $this->dispatch('category-added');
    }

    public function with()
    {
        return [
            'categories' => Category::orderBy('name')->get(),
        ];
    }
};

?>


<div x-data="{ showNewCat: false }"
     x-on:category-added.window="showNewCat = false"
     class="p-6 gothic-theme">

    <flux:header class="mb-8 border-b border-black dark:border-white pb-4">
        <flux:heading size="xl" class="uppercase italic font-black tracking-tighter">
            01 / Identity Registry
        </flux:heading>

        <flux:subheading class="uppercase text-[9px] tracking-[0.3em]">
            Establish core merchandise data
        </flux:subheading>
    </flux:header>

    <form wire:submit="createDraft" class="space-y-8">

        {{-- ROW 1: IDENTITY & GENDER --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

            <flux:input
                wire:model="name"
                label="Product Name"
                placeholder="e.g. TNC Essential Hoodie"
            />

            <div class="space-y-2">
                <flux:label>Target Gender</flux:label>

                <flux:select wire:model="gender">
                    <flux:select.option value="unisex">Unisex / Neutral</flux:select.option>
                    <flux:select.option value="male">Menswear</flux:select.option>
                    <flux:select.option value="female">Womenswear</flux:select.option>
                </flux:select>
            </div>

        </div>

        {{-- ROW 2: CATEGORY --}}
        <div class="space-y-2">
            <flux:label>Category</flux:label>

            <div class="flex gap-2">

                <flux:select
                    wire:model="category_id"
                    class="flex-1"
                    wire:key="category-registry-select"
                >
                    <flux:select.option value="">Select Category</flux:select.option>

                    @foreach(\App\Models\Category::all() as $cat)
                        <flux:select.option value="{{ $cat->id }}">
                            {{ $cat->name }}
                        </flux:select.option>
                    @endforeach
                </flux:select>

                <flux:button
                    type="button"
                    variant="ghost"
                    icon="plus"
                    x-on:click="showNewCat = !showNewCat"
                />
            </div>
        </div>

        {{-- QUICK CATEGORY TOOL --}}
        <div x-show="showNewCat"
             x-collapse
             class="p-4 bg-zinc-50 dark:bg-zinc-900 border border-black/10 flex gap-3 items-end">

            <flux:input
                wire:model="new_category_name"
                label="New Category Name"
                class="flex-1"
            />

            <flux:button
                type="button"
                wire:click="addCategory"
                size="sm"
                class="bg-black text-white dark:bg-white dark:text-black rounded-none"
            >
                Add
            </flux:button>

        </div>

        {{-- ROW 3: PRICING --}}
        <div class="grid grid-cols-2 gap-8">

            <flux:input
                wire:model="base_price"
                type="number"
                step="0.01"
                label="Retail Price"
            />

            <flux:input
                wire:model="compare_at_price"
                type="number"
                step="0.01"
                label="Compare Price ($)"
                icon="currency-dollar"
            />

        </div>

        {{-- ROW 4: DESCRIPTION --}}
        <flux:textarea
            wire:model="description"
            label="Brand Description"
            rows="3"
        />

        {{-- ROW 5: SPEC META --}}
        <div class="grid grid-cols-2 gap-8">

            <flux:input
                wire:model="material"
                label="Material"
            />

            <flux:input
                wire:model="fit_type"
                label="Fit / Silhouette"
            />

        </div>

        {{-- SUBMIT --}}
        <div class="pt-6 border-t border-black/10 flex justify-end">

            <flux:button
                type="submit"
                class="bg-black text-white dark:bg-white dark:text-black px-16 h-14 uppercase tracking-[0.4em] font-black text-[11px] hover:invert rounded-none"
            >
                Register Draft
            </flux:button>

        </div>

    </form>
</div>
