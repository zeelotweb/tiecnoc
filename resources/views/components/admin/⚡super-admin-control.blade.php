<?php

use Livewire\Component;
use App\Models\User;
use App\Services\Admin\AccessControlService;
use App\Services\UserAccessService;

new class extends Component {

    public $users;

    public function mount()
    {
        $user = auth()->user();

        // 🔒 Admin environment gate
        abort_unless($user && app(UserAccessService::class)->canAccessAdmin($user), 403);

        // 🔒 Only super admin can access this component
        abort_unless(app(UserAccessService::class)->isSuperAdmin($user), 403);

        $this->users = User::with('tools')->latest()->get();
    }

    /*
    |--------------------------------------------------------------------------
    | ROLE CONTROL (SUPER ADMIN ONLY)
    |--------------------------------------------------------------------------
    */
    public function setRole($userId, $role, AccessControlService $service)
    {
        // allowed transitions
        $allowed = ['admin', 'staff', 'customer'];

        if (!in_array($role, $allowed)) {
            abort(403);
        }

        // super admin only protection still handled in service
        $service->assignRole($userId, $role);

        $this->dispatch('notify', message: 'ROLE UPDATED', type: 'success');

        $this->users = User::with('tools')->latest()->get();
    }

    /*
    |--------------------------------------------------------------------------
    | TOOL CONTROL (ADMIN + SUPER ADMIN)
    |--------------------------------------------------------------------------
    */
    public function toggleTool($userId, $tool, AccessControlService $service)
    {
        $user = User::with('tools')->findOrFail($userId);

        if ($user->tools->contains('tool', $tool)) {
            $service->revokeTool($userId, $tool);
        } else {
            $service->grantTool($userId, $tool);
        }

        $this->users = User::with('tools')->latest()->get();
    }
};
?>


<div class="space-y-6">

    @foreach($users as $u)
        <div class="flex border p-4 flex-col">

            <div class="flex flex-col">
                <p class="flex font-bold">{{ $u->name }}</p>
                <p class="flex text-xs opacity-40">{{ $u->email }}</p>
                <p class="flex text-[10px] uppercase">{{ $u->role }}</p>
            </div>

{{-- ROLE CONTROL (SUPER ADMIN ONLY) --}}
@if(auth()->user()->isSuperAdmin())
    <div class="flex gap-2 mt-2">

        <button wire:click="setRole({{ $u->id }}, 'admin')">
            Admin
        </button>

        <button wire:click="setRole({{ $u->id }}, 'staff')">
            Staff
        </button>

        {{-- RESET TO NON-ADMIN ENV STATE --}}
        <button wire:click="setRole({{ $u->id }}, 'customer')">
            Remove
        </button>

    </div>
@endif
            {{-- TOOL CONTROL (ADMIN + SUPER ADMIN) --}}
            @if(auth()->user()->isAdmin())
                <div class="flex gap-2 mt-3">
                    @foreach(['media','specs','editor'] as $tool)
                        <button wire:click="toggleTool({{ $u->id }}, '{{ $tool }}')">
                            {{ $tool }}
                        </button>
                    @endforeach
                </div>
            @endif

        </div>
    @endforeach

</div>