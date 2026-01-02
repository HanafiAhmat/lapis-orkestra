<?php declare(strict_types=1);

use BitSynama\Lapis\Framework\Foundation\Env;

/**
 * Global middleware (runs on every request, before route‐specific middleware).
 * Each entry is a [middleware_id, [arg1, arg2, …]] pair.
 */
return [
    // e.g. throttle requests globally
    'throttle' => [
        'max_requests' => Env::int('MIDDLEWARE_THROTTLE_MAX_REQUESTS', 100),
        'decay' => Env::int('MIDDLEWARE_THROTTLE_DECAY', 60),
    ],

    // e.g. a CORS middleware
    'cors' => [
        'allowed_origin' => Env::array(
            'MIDDLEWARE_CORS_ALLOWED_ORIGINS',
            ['capacitor://localhost', 'ionic://localhost', 'http://localhost', 'https://localhost']
        ),
    ],

    'login' => [
        'max_attempts' => Env::int('MIDDLEWARE_LOGIN_MAX_ATTEMPTS', 5),
        'decay' => Env::int('MIDDLEWARE_LOGIN_DECAY', 60),
        'burst_multiplier' => Env::float('MIDDLEWARE_LOGIN_BURST_MULTIPLIER', 1.5),
    ],

    'rate_limit' => [
        'max_requests' => Env::int('MIDDLEWARE_RATE_LIMIT_MAX_REQUESTS', 5),
        'decay' => Env::int('MIDDLEWARE_RATE_LIMIT_DECAY', 60),
        'burst_multiplier' => Env::float('MIDDLEWARE_RATE_LIMIT_BURST_MULTIPLIER', 1.5),
    ],

    'ssl' => [
        'allowed_domains' => Env::array(
            'MIDDLEWARE_SSL_ALLOWED_DOMAINS',
            ['CN=bitsynama.local', 'CN=bitsynama.dev', 'CN=localhost']
        ),
        'allowed_issuers' => Env::array('MIDDLEWARE_SSL_ALLOWED_ISSUERS', ['O=TrustedSSL Authority', 'O=Lets Encrypt']),
    ],

    'ip_access' => [
        'allowed_ips' => Env::array('MIDDLEWARE_IP_ACCESS_ALLOWED_IPS', []),
        'blocked_ips' => Env::array('MIDDLEWARE_IP_ACCESS_BLOCKED_IPS', []),
    ],
];
