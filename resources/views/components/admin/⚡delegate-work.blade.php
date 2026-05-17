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


<div class="space-y-6">

    @foreach($staff as $member)
        <div class="border border-black dark:border-white p-4">

            {{-- NAME --}}
            <div class="mb-4">
                <p class="font-black uppercase text-sm">
                    {{ $member->name }}
                </p>
                <p class="text-[10px] opacity-40">
                    {{ $member->email }}
                </p>
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
                        class="px-3 py-2 text-[10px] font-black uppercase tracking-widest border-2 transition-all
                        {{ $assigned 
                            ? 'bg-emerald-500 text-white border-emerald-500' 
                            : 'border-black dark:border-white opacity-40 hover:opacity-100' }}"
                    >
                        {{ $label }}
                    </button>

                @endforeach

            </div>

        </div>
    @endforeach

</div>