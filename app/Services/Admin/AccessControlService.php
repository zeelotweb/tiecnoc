<?php

namespace App\Services\Admin;

use App\Models\User;
use App\Services\UserAccessService;
use Illuminate\Support\Facades\Auth;

class AccessControlService
{
    public function __construct(
        protected UserAccessService $access
    ) {}

    /*
    |--------------------------------------------------------------------------
    | ASSIGN ROLE (SUPER ONLY)
    |--------------------------------------------------------------------------
    */
    public function assignRole(int $userId, string $role): void
    {
        $actor = Auth::user();

        if (!$actor || !$this->access->canManageRoles($actor)) {
            abort(403);
        }

        // 🔒 HARD LOCK SUPER ADMIN IDENTITY
        if ($userId === 1) {
            abort(403, 'Super admin cannot be modified.');
        }

        User::findOrFail($userId)
            ->update(['role' => $role]);
    }
}