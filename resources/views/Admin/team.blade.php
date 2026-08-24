<x-layouts::admin>
    <x-slot:pageEyebrow>{{ __('Organization') }}</x-slot:pageEyebrow>
    <x-slot:pageTitle>{{ __('Crew') }}</x-slot:pageTitle>

    @if(auth()->user()->isSuperAdmin())
        <livewire:admin.super-admin-control />
    @endif
</x-layouts::admin>
