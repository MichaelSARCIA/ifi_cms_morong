<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class EnsureUserIsActive
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            
            // Check if user's role still exists (unless Admin)
            if ($user->role !== 'Admin') {
                $roleExists = \App\Models\Role::where('name', $user->role)->exists();
                if (!$roleExists) {
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                    
                    return redirect()->route('login')->withErrors([
                        'email' => 'Your session has ended because your account role is no longer active. Please contact the administrator.'
                    ]);
                }
            }

            if ($user->status !== 'Active') {
                $user->update(['status' => 'Active']);
            }
        }

        return $next($request);
    }
}
