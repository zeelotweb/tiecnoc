<?php

namespace App\Services\Admin;

use App\Models\User;
use App\Models\UserToolPermission;
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

    /*
    |--------------------------------------------------------------------------
    | GRANT TOOL (ADMIN + SUPER)
    |--------------------------------------------------------------------------
    */
    public function grantTool(int $userId, string $tool): void
    {
        $actor = Auth::user();

        if (!$actor || !$this->access->canManageTools($actor)) {
            abort(403);
        }

        // 🔒 OPTIONAL SAFETY (prevents accidental tampering patterns)
        if ($userId === 1) {
            abort(403, 'Super admin tools are system-controlled.');
        }

        UserToolPermission::firstOrCreate([
            'user_id' => $userId,
            'tool' => $tool
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | REVOKE TOOL (ADMIN + SUPER)
    |--------------------------------------------------------------------------
    */
    public function revokeTool(int $userId, string $tool): void
    {
        $actor = Auth::user();

        if (!$actor || !$this->access->canManageTools($actor)) {
            abort(403);
        }

        if ($userId === 1) {
            abort(403, 'Super admin tools are system-controlled.');
        }

        UserToolPermission::where([
            'user_id' => $userId,
            'tool' => $tool
        ])->delete();
    }
}