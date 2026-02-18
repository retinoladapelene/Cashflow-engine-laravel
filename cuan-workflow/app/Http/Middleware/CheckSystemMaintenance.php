<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSystemMaintenance
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip check for login, admin routes, and /up health check
        if ($request->is('login*') || $request->is('admin*') || $request->is('up') || $request->is('api/login*') || $request->is('api/me') || $request->is('api/user')) {
            return $next($request);
        }

        // Check if user is admin
        if ($request->user() && $request->user()->role === 'admin') {
            return $next($request);
        }

        $maintenance = \App\Models\SystemSetting::where('key', 'system_maintenance')->first();
        if ($maintenance && $maintenance->value === '1') {
            // If it's an API request, return JSON
            if ($request->expectsJson()) {
                return response()->json(['message' => 'System under maintenance.'], 503);
            }
            
            return response()->view('maintenance', [], 503);
        }

        return $next($request);
    }
}
