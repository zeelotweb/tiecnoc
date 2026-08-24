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

        $this->users = User::latest()->get();
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

        $this->users = User::latest()->get();
    }

};
?>


<div class="space-y-4">

    @forelse($users as $u)
        <div class=" border border-black/15 dark:border-white/15 p-4 space-y-3">

            <div class="flex items-center justify-between">
                <div>
                    <p class="font-medium text-sm">{{ $u->name }}</p>
                    <p class="text-xs text-zinc-500">{{ $u->email }}</p>
                </div>
                <flux:badge :color="in_array($u->role, ['admin', 'super_admin']) ? 'emerald' : 'zinc'" size="sm">
                    {{ str_replace('_', ' ', $u->role) }}
                </flux:badge>
            </div>

            {{-- ROLE CONTROL (SUPER ADMIN ONLY) --}}
            @if(auth()->user()->isSuperAdmin())
                <div class="flex gap-2">
                    @foreach(['admin' => 'Admin', 'staff' => 'Staff', 'customer' => 'Remove'] as $role => $label)
                        <button wire:click="setRole({{ $u->id }}, '{{ $role }}')"
                            class="px-3 py-1.5 text-xs  border transition-colors
                            {{ $u->role === $role
                                ? 'bg-zinc-900 text-white border-zinc-900 dark:bg-white dark:text-zinc-900 dark:border-white'
                                : 'border-black/15 dark:border-white/15 text-zinc-500 hover:border-black/40 dark:hover:border-white/40' }}">
                            {{ $label }}
                        </button>
                    @endforeach
                </div>
            @endif

        </div>
    @empty
        <p class="text-sm text-zinc-500">No users yet.</p>
    @endforelse

</div>