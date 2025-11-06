<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class BlockSuspiciousRequests
{
    /**
     * Suspicious patterns to block
     */
    protected array $suspiciousPatterns = [
        // SQL Injection attempts
        '/(\bUNION\b.*\bSELECT\b)|(\bSELECT\b.*\bFROM\b)/i',
        '/(\bDROP\b.*\bTABLE\b)|(\bINSERT\b.*\bINTO\b)/i',
        '/(\bDELETE\b.*\bFROM\b)|(\bUPDATE\b.*\bSET\b)/i',

        // XSS attempts
        '/<script[^>]*>.*?<\/script>/i',
        '/javascript:/i',
        '/onerror\s*=/i',
        '/onclick\s*=/i',

        // Path traversal
        '/\.\.\//',
        '/\.\.\\/i',

        // Command injection
        '/;\s*(ls|cat|rm|chmod|wget|curl)/i',
        '/\|\s*(ls|cat|rm|chmod|wget|curl)/i',

        // Common attack tools
        '/sqlmap/i',
        '/nikto/i',
        '/nmap/i',
    ];

    /**
     * Blocked user agents
     */
    protected array $blockedUserAgents = [
        'sqlmap',
        'nikto',
        'nmap',
        'masscan',
        'zgrab',
        'python-requests', // Often used by bots
        'curl',
        'wget',
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check for suspicious patterns in URL and inputs
        if ($this->hasSuspiciousPatterns($request)) {
            $this->logSuspiciousRequest($request, 'Suspicious pattern detected');
            abort(403, 'Forbidden');
        }

        // Check for blocked user agents
        if ($this->hasBlockedUserAgent($request)) {
            $this->logSuspiciousRequest($request, 'Blocked user agent');
            abort(403, 'Forbidden');
        }

        // Check for excessive request size (potential DoS)
        if ($this->isRequestTooLarge($request)) {
            $this->logSuspiciousRequest($request, 'Request too large');
            abort(413, 'Payload Too Large');
        }

        return $next($request);
    }

    /**
     * Check if request contains suspicious patterns
     */
    protected function hasSuspiciousPatterns(Request $request): bool
    {
        // Check URL
        $url = $request->fullUrl();
        foreach ($this->suspiciousPatterns as $pattern) {
            if (preg_match($pattern, $url)) {
                return true;
            }
        }

        // Check all input data
        $inputs = $request->all();
        foreach ($inputs as $value) {
            if (is_string($value)) {
                foreach ($this->suspiciousPatterns as $pattern) {
                    if (preg_match($pattern, $value)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Check if user agent is blocked
     */
    protected function hasBlockedUserAgent(Request $request): bool
    {
        $userAgent = strtolower($request->userAgent() ?? '');

        foreach ($this->blockedUserAgents as $blocked) {
            if (str_contains($userAgent, strtolower($blocked))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if request is too large
     */
    protected function isRequestTooLarge(Request $request): bool
    {
        // Check if content length exceeds 10MB
        $contentLength = $request->header('Content-Length', 0);
        return $contentLength > 10 * 1024 * 1024;
    }

    /**
     * Log suspicious request
     */
    protected function logSuspiciousRequest(Request $request, string $reason): void
    {
        Log::channel('security')->warning('Suspicious Request Blocked', [
            'reason' => $reason,
            'ip' => $request->ip(),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'user_agent' => $request->userAgent(),
            'inputs' => $request->except(['password', 'password_confirmation']),
        ]);
    }
}
