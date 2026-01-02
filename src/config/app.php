<?php declare(strict_types=1);

use BitSynama\Lapis\Framework\Foundation\Env;

return [
    // The environment: production|staging|development|testing
    'env' => Env::string('APP_ENV', 'development'),

    // Turn on (true) to display errors/extra debug info
    'debug' => Env::bool('APP_DEBUG', true),

    // Application name (for email subjects, page titles, etc.)
    'name' => Env::string('APP_NAME', 'Lapis Orkestra'),

    // Default JSON encoding flags (used by MultiResponse)
    'json_options' => Env::int('APP_JSON_OPTIONS', JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK),

    'encryption_key' => Env::string('APP_ENCRYPTION_KEY', 'please-change-me-1234'),

    'token_lifetime' => [
        'access' => Env::string('APP_ACCESS_TOKEN_LIFETIME', '1 day'),
        'refresh' => Env::string('APP_REFRESH_TOKEN_LIFETIME', '7 days'),
        'password_reset' => Env::string('APP_PASSWORD_RESET_TOKEN_LIFETIME', '1 hour'),
        'email_verification' => Env::string('APP_EMAIL_VERIFICATION_TOKEN_LIFETIME', '3 days'),
    ],

    'allowed_audiences' => Env::array('APP_ALLOWED_AUDIENCES', ['web', 'mobile', 'postman']),
    'jwt_issuer' => Env::string('APP_JWT_ISSUER', 'http://localhost'),

    // The URL prefix under which all “admin” routes live.
    // Could be '/admin', '/backoffice', or even 'admin-site.com' if you want separate domain.
    'routes' => [
        'admin_prefix' => Env::string('APP_ADMIN_PREFIX', '/admin'),
    ],

    'audit_log_enabled' => Env::bool('APP_AUDIT_LOG_ENABLED', true),

    'cdn_url' => Env::string('APP_CDN_URL', 'http://localhost:8000'),
    'frontend_url' => Env::string('APP_FRONTEND_URL', 'http://localhost:8000'),
];
