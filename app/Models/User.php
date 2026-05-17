<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Cashier\Billable;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, TwoFactorAuthenticatable, Billable;

    /*
    |--------------------------------------------------------------------------
    | MASS ASSIGNABLE
    |--------------------------------------------------------------------------
    */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'balance',
    ];

    /*
    |--------------------------------------------------------------------------
    | HIDDEN ATTRIBUTES
    |--------------------------------------------------------------------------
    */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /*
    |--------------------------------------------------------------------------
    | CASTS
    |--------------------------------------------------------------------------
    */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'balance' => 'decimal:2',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | BOOT - FORCE SUPER ADMIN
    |--------------------------------------------------------------------------
    */
protected static function booted()
{
    static::retrieved(function ($user) {

        // 🔒 enforce super admin identity once loaded
        if ($user->id === 1 && $user->role !== 'super_admin') {
            $user->forceFill([
                'role' => 'super_admin'
            ])->saveQuietly();
        }
    });

    static::saving(function ($user) {

        // 🔒 prevent override
        if ($user->id === 1) {
            $user->role = 'super_admin';
        }
    });
}
    /*
    |--------------------------------------------------------------------------
    | ROLE CHECKS
    |--------------------------------------------------------------------------
    */

    public function isSuperAdmin(): bool
    {
        return $this->id === 1 || $this->role === 'super_admin';
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['admin', 'super_admin']) || $this->id === 1;
    }

    public function isStaff(): bool
    {
        return in_array($this->role, ['staff', 'admin', 'super_admin']);
    }

    public function isPartner(): bool
    {
        return $this->role === 'partner';
    }

    public function isContractor(): bool
    {
        return $this->role === 'contractor';
    }

    /*
    |--------------------------------------------------------------------------
    | ADMIN ACCESS (ENVIRONMENT GATE)
    |--------------------------------------------------------------------------
    */
    public function canAccessAdmin(): bool
    {
        return in_array($this->role, ['staff', 'admin', 'super_admin']) || $this->id === 1;
    }

    /*
    |--------------------------------------------------------------------------
    | TOOL PERMISSIONS
    |--------------------------------------------------------------------------
    */
    public function tools()
    {
        return $this->hasMany(UserToolPermission::class);
    }

    public function hasTool(string $tool): bool
    {
        if ($this->isSuperAdmin() || $this->isAdmin()) {
            return true;
        }

        return $this->tools()->where('tool', $tool)->exists();
    }

    /*
    |--------------------------------------------------------------------------
    | USER UTILITY
    |--------------------------------------------------------------------------
    */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }
}