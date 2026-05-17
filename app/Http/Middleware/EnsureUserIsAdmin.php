<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect('/login');
        }

        $user = auth()->user();

        // 🔒 ONLY allow admin environment roles
        if (!$user->canAccessAdmin()) {
            abort(403); // cleaner than redirect for protected domain
        }

        return $next($request);
    }
}