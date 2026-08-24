<x-layouts::admin>
    <x-slot:pageEyebrow>{{ __('Organization') }}</x-slot:pageEyebrow>
    <x-slot:pageTitle>{{ __('Team') }}</x-slot:pageTitle>

    <div class="space-y-12">
        @if(auth()->user()->isSuperAdmin())
            <div class="space-y-4">
                <p class="text-sm font-medium border-b border-black/15 dark:border-white/15 pb-3">Roles</p>
                <livewire:admin.super-admin-control />
            </div>
        @endif

        <div class="space-y-4">
            <p class="text-sm font-medium border-b border-black/15 dark:border-white/15 pb-3">Tool Delegation</p>
            <livewire:admin.delegate-work />
        </div>
    </div>
</x-layouts::admin>
