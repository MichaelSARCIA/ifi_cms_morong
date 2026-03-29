<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckModuleAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $module): Response
    {
        if (!$request->user() || !$request->user()->hasModule($module)) {
            // If it's an API request or expects JSON, return 403
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Forbidden Access to Module: ' . ucfirst($module)], 403);
            }

            $dashboardRoute = 'dashboard';

            return redirect()->route($dashboardRoute)->with('error', 'You do not have access to the ' . ucfirst($module) . ' module.');
        }

        return $next($request);
    }
}
