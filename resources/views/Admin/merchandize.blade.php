<x-layouts::admin>
    <x-slot:pageEyebrow>{{ __('Catalog') }}</x-slot:pageEyebrow>
    <x-slot:pageTitle>{{ __('The Line') }}</x-slot:pageTitle>
    <x-slot:pageActions>
        @if(auth()->user()->isAdmin())
            <flux:button x-on:click="$flux.modal('add-product-modal').show()" icon="plus" variant="primary">
                Add Product
            </flux:button>
        @endif
    </x-slot:pageActions>

    @livewire('admin.merchandise.index')

    @include('partials.admin.product-create-modal')
    @include('partials.admin.product-tools-modal')
</x-layouts::admin>
