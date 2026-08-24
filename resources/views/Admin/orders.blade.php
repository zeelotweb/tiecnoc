<x-layouts::admin>
    <x-slot:pageEyebrow>{{ __('Sales') }}</x-slot:pageEyebrow>
    <x-slot:pageTitle>{{ __('Orders') }}</x-slot:pageTitle>

    @livewire('admin.dashboard.invoice')
</x-layouts::admin>
