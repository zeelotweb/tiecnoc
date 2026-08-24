{{-- Shared "edit product" modal — included once per page that needs it
     (products list, dashboard). Row actions dispatch load-*-tool events with
     a product id and open this modal; the tab components inside hydrate
     themselves from those events (see their #[On(...)] listeners). --}}
<flux:modal name="product-edit-modal" class="w-full max-w-3xl py-10" flyout>
    <div x-data="{ tab: 'overview' }">

        <div class="flex flex-wrap gap-1 border-b border-black/15 dark:border-white/15 mb-8">
            @foreach([
                'overview'   => 'Overview',
                'colorways'  => 'Colorways',
                'variants'   => 'Variants & Stock',
                'gallery'    => 'Gallery',
                'metrics'    => 'Metrics',
            ] as $key => $label)
                <button
                    type="button"
                    x-on:click="tab = '{{ $key }}'"
                    :class="tab === '{{ $key }}' ? 'border-b-2 border-[#E31837] text-zinc-900 dark:text-white' : 'border-b-2 border-transparent text-zinc-500 hover:text-zinc-900 dark:hover:text-white'"
                    class="px-3 py-2.5 text-sm font-medium transition-colors -mb-px">
                    {{ $label }}
                </button>
            @endforeach
        </div>

        <div x-show="tab === 'overview'">
            @livewire('admin.tools.editor')
        </div>

        <div x-show="tab === 'colorways'">
            @livewire('admin.tools.visuals')
        </div>

        <div x-show="tab === 'variants'">
            @livewire('admin.tools.specs')
        </div>

        <div x-show="tab === 'gallery'" class="space-y-10">
            @livewire('admin.tools.gallery')
            @livewire('admin.media.gallery')
        </div>

        <div x-show="tab === 'metrics'">
            @livewire('admin.tools.metrics')
        </div>

    </div>
</flux:modal>
