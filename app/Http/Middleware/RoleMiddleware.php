<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param  \Closure  $next
     * @param string $role
     * @return mixed
     */
    public function handle(\Illuminate\Http\Request $request, Closure $next, string $role): mixed
    {
        // Проверяем, авторизован ли пользователь и соответствует ли его роль требуемой
        if (!Auth::check() || Auth::user()->role !== $role) {
            abort(403, 'Access denied');
        }

        return $next($request);
    }
}
