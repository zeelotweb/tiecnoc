<?php

namespace App\Services;

use App\Models\User;

class UserAccessService
{
    public function isSuperAdmin(User $user): bool
    {
        return $user->id === 1 || $user->role === 'super_admin';
    }

    public function canManageRoles(User $user): bool
    {
        return $this->isSuperAdmin($user);
    }

    public function canManageTools(User $user): bool
    {
        return in_array($user->role, ['admin', 'super_admin']) || $user->id === 1;
    }

    public function canAccessAdmin(User $user): bool
    {
        return in_array($user->role, ['staff', 'admin', 'super_admin']) || $user->id === 1;
    }
}