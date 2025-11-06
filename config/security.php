<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Security Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains all security-related configuration for the application.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Configure rate limiting thresholds for different types of requests.
    |
    */
    'rate_limiting' => [
        'web' => [
            'requests' => env('RATE_LIMIT_WEB_REQUESTS', 60),
            'minutes' => env('RATE_LIMIT_WEB_MINUTES', 1),
        ],
        'auth' => [
            'requests' => env('RATE_LIMIT_AUTH_REQUESTS', 5),
            'minutes' => env('RATE_LIMIT_AUTH_MINUTES', 1),
        ],
        'api' => [
            'requests' => env('RATE_LIMIT_API_REQUESTS', 60),
            'minutes' => env('RATE_LIMIT_API_MINUTES', 1),
        ],
        'sensitive' => [
            'requests' => env('RATE_LIMIT_SENSITIVE_REQUESTS', 3),
            'minutes' => env('RATE_LIMIT_SENSITIVE_MINUTES', 1),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Headers
    |--------------------------------------------------------------------------
    |
    | Configure security headers that will be applied to all responses.
    |
    */
    'headers' => [
        'x_frame_options' => env('SECURITY_X_FRAME_OPTIONS', 'SAMEORIGIN'),
        'x_content_type_options' => env('SECURITY_X_CONTENT_TYPE_OPTIONS', 'nosniff'),
        'x_xss_protection' => env('SECURITY_X_XSS_PROTECTION', '1; mode=block'),
        'strict_transport_security' => env('SECURITY_HSTS', 'max-age=31536000; includeSubDomains'),
        'referrer_policy' => env('SECURITY_REFERRER_POLICY', 'strict-origin-when-cross-origin'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Content Security Policy
    |--------------------------------------------------------------------------
    |
    | Configure Content Security Policy directives.
    |
    */
    'csp' => [
        'enabled' => env('CSP_ENABLED', true),
        'default_src' => "'self'",
        'script_src' => "'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net",
        'style_src' => "'self' 'unsafe-inline' https://fonts.bunny.net https://cdn.jsdelivr.net",
        'font_src' => "'self' https://fonts.bunny.net data:",
        'img_src' => "'self' data: https: http:",
        'connect_src' => "'self'",
        'frame_ancestors' => "'self'",
    ],

    /*
    |--------------------------------------------------------------------------
    | Suspicious Request Blocking
    |--------------------------------------------------------------------------
    |
    | Enable/disable automatic blocking of suspicious requests.
    |
    */
    'block_suspicious_requests' => [
        'enabled' => env('BLOCK_SUSPICIOUS_REQUESTS', true),
        'log_attempts' => env('LOG_SUSPICIOUS_ATTEMPTS', true),
        'max_request_size' => env('MAX_REQUEST_SIZE', 10 * 1024 * 1024), // 10MB
    ],

    /*
    |--------------------------------------------------------------------------
    | IP Whitelist
    |--------------------------------------------------------------------------
    |
    | List of IP addresses that should bypass certain security checks.
    | Useful for trusted internal networks or development environments.
    |
    */
    'ip_whitelist' => array_filter(explode(',', env('IP_WHITELIST', ''))),

    /*
    |--------------------------------------------------------------------------
    | IP Blacklist
    |--------------------------------------------------------------------------
    |
    | List of IP addresses that should be blocked from accessing the application.
    |
    */
    'ip_blacklist' => array_filter(explode(',', env('IP_BLACKLIST', ''))),

    /*
    |--------------------------------------------------------------------------
    | Session Security
    |--------------------------------------------------------------------------
    |
    | Configure session security settings.
    |
    */
    'session' => [
        'lifetime' => env('SESSION_LIFETIME', 120), // minutes
        'secure' => env('SESSION_SECURE_COOKIE', false), // Set to true in production with HTTPS
        'http_only' => env('SESSION_HTTP_ONLY', true),
        'same_site' => env('SESSION_SAME_SITE', 'lax'), // lax, strict, or none
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Security
    |--------------------------------------------------------------------------
    |
    | Configure password requirements and policies.
    |
    */
    'password' => [
        'min_length' => env('PASSWORD_MIN_LENGTH', 8),
        'require_uppercase' => env('PASSWORD_REQUIRE_UPPERCASE', true),
        'require_lowercase' => env('PASSWORD_REQUIRE_LOWERCASE', true),
        'require_numbers' => env('PASSWORD_REQUIRE_NUMBERS', true),
        'require_special_chars' => env('PASSWORD_REQUIRE_SPECIAL', false),
        'prevent_common' => env('PASSWORD_PREVENT_COMMON', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Failed Login Attempts
    |--------------------------------------------------------------------------
    |
    | Configure how failed login attempts are handled.
    |
    */
    'failed_login' => [
        'max_attempts' => env('MAX_LOGIN_ATTEMPTS', 5),
        'lockout_duration' => env('LOGIN_LOCKOUT_DURATION', 60), // seconds
        'notify_admin' => env('NOTIFY_ADMIN_FAILED_LOGINS', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Two-Factor Authentication
    |--------------------------------------------------------------------------
    |
    | Enable/disable two-factor authentication (requires additional setup).
    |
    */
    '2fa' => [
        'enabled' => env('TWO_FACTOR_ENABLED', false),
        'required_for_admin' => env('TWO_FACTOR_REQUIRED_ADMIN', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Backup Security
    |--------------------------------------------------------------------------
    |
    | Configure backup encryption and storage.
    |
    */
    'backup' => [
        'encrypt' => env('BACKUP_ENCRYPT', false),
        'notification_email' => env('BACKUP_NOTIFICATION_EMAIL', ''),
    ],

];
