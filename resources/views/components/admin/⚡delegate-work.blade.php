<?php

use Livewire\Component;
use App\Models\User;
use App\Services\Admin\AccessControlService;
use App\Services\UserAccessService;

new class extends Component {

    public $staff;

    // MUST be public for Blade access
    public array $tools = [
        'media'   => 'Add Visuals',
        'specs'   => 'Manage Specs',
        'editor'  => 'Edit Info',
        'metrics' => 'Metrics',
        'gallery' => 'Add Media',
        'view'    => 'View Media',
        'toggle'  => 'Toggle Availability',
    ];

    public function mount()
    {
        $user = auth()->user();

        abort_unless(
            $user && app(UserAccessService::class)->canAccessAdmin($user),
            403
        );

        $this->loadStaff();
    }

    public function loadStaff()
    {
        $this->staff = User::whereIn('role', ['staff', 'admin'])
            ->with('tools')
            ->get();
    }

    public function toggleTool($userId, $tool)
    {
        $service = app(AccessControlService::class);

        $user = User::with('tools')->findOrFail($userId);

        if ($user->tools->contains('tool', $tool)) {
            $service->revokeTool($userId, $tool);
        } else {
            $service->grantTool($userId, $tool);
        }

        $this->loadStaff();
    }
};
?>


<div class="space-y-4">

    @forelse($staff as $member)
        <div class=" border border-black/15 dark:border-white/15 p-4">

            {{-- NAME --}}
            <div class="mb-3">
                <p class="font-medium text-sm">{{ $member->name }}</p>
                <p class="text-xs text-zinc-500">{{ $member->email }}</p>
            </div>

            {{-- DUTIES --}}
            <div class="flex flex-wrap gap-2">

                @foreach($tools as $key => $label)

                    @php
                        $assigned = $member->tools
                            ?->pluck('tool')
                            ->contains($key) ?? false;
                    @endphp

                    <button
                        wire:click="toggleTool({{ $member->id }}, '{{ $key }}')"
                        class="px-3 py-1.5 text-xs  border transition-colors
                        {{ $assigned
                            ? 'bg-emerald-500 text-white border-emerald-500'
                            : 'border-black/15 dark:border-white/15 text-zinc-500 hover:border-black/40 dark:hover:border-white/40' }}"
                    >
                        {{ $label }}
                    </button>

                @endforeach

            </div>

        </div>
    @empty
        <p class="text-sm text-zinc-500">No staff members yet.</p>
    @endforelse

</div>