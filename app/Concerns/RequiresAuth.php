<?php

namespace App\Concerns;

use Illuminate\Support\Facades\Auth;

trait RequiresAuth
{ /*
    protected function requireAuth(): bool
    {
        if (!Auth::check()) {
            $this->dispatch('open-modal', 'auth-modal');
            return false;
        }

        return true;
    }
    */
}