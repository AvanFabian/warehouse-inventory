<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Owner/Admin Only Middleware
 * 
 * Restricts access to admin users only.
 * Used for high-value features like:
 * - Dashboard Analytics
 * - Currency Settings
 * - Audit Logs
 * - Stock Opname
 */
class OwnerMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // Admin and manager roles have owner-level access
        if ($user->isAdmin() || $user->isManager()) {
            return $next($request);
        }

        abort(403, 'Access denied. Owner privileges required.');
    }
}
