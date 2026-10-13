<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()) {
            $role = $request->user()->getPrimaryRole();
            
            return match ($role) {
                'Super Admin' => redirect()->route('superadmin.dashboard'),
                'Admin' => redirect()->route('admin.dashboard'),
                'Evaluador' => redirect()->route('evaluator.dashboard'),
                'Docente' => redirect()->route('teacher.dashboard'),
                default => redirect()->route('inicio.dashboard'),
            };
        }

        return $next($request);
    }
}
