<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogUserActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Log user activity for specific actions
        if ($this->shouldLog($request)) {
            $this->logActivity($request);
        }

        return $next($request);
    }

    /**
     * Determine if the request should be logged
     */
    protected function shouldLog(Request $request): bool
    {
        // Only log authenticated users
        if (!Auth::check()) {
            return false;
        }

        // Only log specific methods (POST, PUT, DELETE)
        if (!in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            return false;
        }

        // Don't log certain routes
        $excludedRoutes = [
            'logout',
            'password.*',
            '_ignition.*',
        ];

        foreach ($excludedRoutes as $pattern) {
            if ($request->routeIs($pattern)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Log the user activity
     */
    protected function logActivity(Request $request): void
    {
        $action = $this->getActionName($request);
        $user = Auth::user();

        Log::channel('activity')->info($action, [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'user_role' => $user->role,
            'action' => $action,
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'route' => $request->route()?->getName(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Get a human-readable action name
     */
    protected function getActionName(Request $request): string
    {
        $method = $request->method();
        $routeName = $request->route()?->getName();

        if (!$routeName) {
            return $method . ' ' . $request->path();
        }

        // Map common route patterns to actions
        $actions = [
            'store' => 'Created',
            'update' => 'Updated',
            'destroy' => 'Deleted',
        ];

        foreach ($actions as $pattern => $action) {
            if (str_contains($routeName, $pattern)) {
                $resource = str_replace(['.' . $pattern, '-'], [' ', ' '], $routeName);
                return $action . ' ' . ucwords($resource);
            }
        }

        return 'Action on ' . str_replace('.', ' ', $routeName);
    }
}
