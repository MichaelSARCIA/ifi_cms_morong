<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SingleDeviceSession
{
    /**
     * Handle an incoming request.
     *
     * If the authenticated user's stored session_id does not match the
     * current session, it means they (or an admin) have logged in on
     * another device. Force-logout this session immediately.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();

            // Always allow logout to proceed — if we invalidate the session here
            // before the CSRF middleware sees it, we get a 419 Page Expired.
            if ($request->routeIs('logout')) {
                return $next($request);
            }

            // If no session_id is registered (old session before feature was added) OR
            // if session_id doesn't match the current session (logged in elsewhere),
            // force this device to re-login.
            if (!$user->session_id || $user->session_id !== session()->getId()) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->withErrors([
                    'email' => $user->session_id
                        ? 'This account was logged in on another device. Please log out from there first.'
                        : 'Your session has expired. Please log in again.',
                ]);
            }
        }

        return $next($request);
    }
}
