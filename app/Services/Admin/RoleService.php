<?php
namespace App\Services\Admin;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class RoleService
{
    public function assignRole(int $userId, string $role): void
    {
        $actor = Auth::user();

        if (!$actor || !$actor->isSuperAdmin()) {
            abort(403, 'Only super admin can assign roles.');
        }

        if ($userId === 1) {
            abort(403, 'Cannot modify super admin.');
        }

        $user = User::findOrFail($userId);
        $user->role = $role;
        $user->save();
    }
}