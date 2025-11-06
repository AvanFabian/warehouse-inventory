<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class RateLimitServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Global rate limiter for web routes
        RateLimiter::for('web', function (Request $request) {
            return Limit::perMinute(60)
                ->by($request->user()?->id ?: $request->ip())
                ->response(function (Request $request, array $headers) {
                    return response()->view('errors.429', [], 429)->withHeaders($headers);
                });
        });

        // Stricter rate limit for authentication routes
        RateLimiter::for('auth', function (Request $request) {
            return Limit::perMinute(5)
                ->by($request->ip())
                ->response(function (Request $request, array $headers) {
                    return response()->view('errors.429', [
                        'message' => 'Too many login attempts. Please try again in a few minutes.'
                    ], 429)->withHeaders($headers);
                });
        });

        // Rate limit for API routes (if needed in future)
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)
                ->by($request->user()?->id ?: $request->ip());
        });

        // Very strict rate limit for sensitive operations
        RateLimiter::for('sensitive', function (Request $request) {
            return Limit::perMinute(3)
                ->by($request->user()?->id ?: $request->ip())
                ->response(function (Request $request, array $headers) {
                    return response()->view('errors.429', [
                        'message' => 'Too many attempts. Please wait a few minutes before trying again.'
                    ], 429)->withHeaders($headers);
                });
        });

        // Rate limit for password resets
        RateLimiter::for('password-reset', function (Request $request) {
            return Limit::perHour(3)
                ->by($request->input('email') ?: $request->ip())
                ->response(function (Request $request, array $headers) {
                    return response()->view('errors.429', [
                        'message' => 'Too many password reset requests. Please try again later.'
                    ], 429)->withHeaders($headers);
                });
        });

        // DDoS protection - very aggressive rate limiting
        RateLimiter::for('ddos-protection', function (Request $request) {
            return [
                // 100 requests per minute per IP
                Limit::perMinute(100)->by($request->ip()),
                // 1000 requests per hour per IP
                Limit::perHour(1000)->by($request->ip()),
            ];
        });
    }
}
