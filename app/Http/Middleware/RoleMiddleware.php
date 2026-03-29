<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $userRole = Auth::user()->role;

        // Check if user has the specific role
        // We can expand this to support multiple roles like role:admin,editor
        $roles = explode('|', $role);

        if (in_array($userRole, $roles)) {
            return $next($request);
        }

        // If not authorized, redirect to their own dashboard
        if ($userRole === 'Admin') {
            return redirect()->route('admin.dashboard');
        } elseif ($userRole === 'Treasurer') {
            return redirect()->route('treasurer.dashboard');
        } elseif ($userRole === 'Priest') {
            return redirect()->route('priest.dashboard');
        }

        return redirect()->route('login')->with('error', 'Unauthorized access.');
    }
}
