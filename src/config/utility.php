<?php declare(strict_types=1);

use BitSynama\Lapis\Framework\Foundation\Env;

return [
    'cache' => [
        'adapter' => Env::string('UTILITY_CACHE_ADAPTER', 'file_simple'),
        'namespace' => Env::string('UTILITY_CACHE_NAMESPACE', ''),
        'default_ttl' => Env::int('UTILITY_CACHE_TTL', 3600),
        'cache_dir' => Env::string('UTILITY_CACHE_DIR', ''),
    ],
    'cookie' => [
        'adapter' => Env::string('UTILITY_COOKIE_ADAPTER', 'lapis'),
        'params' => [
            'secure' => Env::bool('UTILITY_COOKIE_USE_SECURE', true),
            'httponly' => true,
            'samesite' => Env::string('UTILITY_COOKIE_SAMESITE', 'Strict'), // Lax
            'domain' => Env::string('UTILITY_COOKIE_DOMAIN', 'localhost'),
            'path' => Env::string('UTILITY_COOKIE_PATH', '/'),
        ],
    ],
    'http_client' => Env::string('UTILITY_HTTP_CLIENT_ADAPTER', 'guzzle'),
    'logger' => [
        'adapter' => Env::string('UTILITY_LOGGER_ADAPTER', 'lapis'),
        'level' => Env::string(
            'UTILITY_LOGGER_LEVEL',
            'debug'
        ), // Log level: debug|info|notice|warning|error|critical|alert|emergency
        'channel' => Env::string('UTILITY_LOGGER_CHANNEL', 'app'),
        'logs_dir' => Env::string('UTILITY_LOGGER_DIR', ''),
    ],
    'request' => Env::string('UTILITY_REQUEST_ADAPTER', 'guzzle'),
    'router' => Env::string('UTILITY_ROUTER_ADAPTER', 'fastroute'),
    'session' => [
        'adapter' => Env::string('UTILITY_SESSION_ADAPTER', 'aura'),
        'name' => Env::string('UTILITY_SESSION_NAME', 'LAPIS_ORKESTRA'),
        'segment' => Env::string('UTILITY_SESSION_SEGMENT', 'LapisOrkestra'),

        //    The key can be matched by path prefix or exact host.
        'variants' => [
            'default' => [
                'name' => Env::string('UTILITY_SESSION_NAME', 'LAPIS_ORKESTRA'),
                'urlPath' => Env::string('UTILITY_SESSION_NAME', 'LAPIS_ORKESTRA'),
                'cookieParams' => [
                    // if you omit a key here, it'll fall back to the global above
                    'path' => Env::string('UTILITY_SESSION_PATH', '/'),
                    'domain' => Env::string('UTILITY_SESSION_DOMAIN', 'localhost'),
                ],
            ],

            // // Path‐based admin UI:
            // 'admin' => [
            //     'path'         => '/admin',
            //     'name'         => 'LAPIS_ADMIN',
            //     'cookieParams' => [
            //         'path' => '/admin',
            //     ],
            // ],

            // // Sub-domain vendor:
            // 'vendor' => [
            //     'path'         => 'vendor.example.com',
            //     'name'         => 'LAPIS_VENDOR',
            //     'cookieParams' => [
            //         'domain' => 'vendor.example.com',
            //         'path'   => '/',
            //     ],
            // ],

            // You can add more...
        ],
    ],
    'view' => [
        'outputs_enabled' => [
            'html' => Env::bool('UTILITY_VIEW_ENABLE_HTML_OUTPUT', true),
            'json' => Env::bool('UTILITY_VIEW_ENABLE_JSON_OUTPUT', true),
        ],

        // Which engine to use: 'lapis', 'twig', or 'plates'
        'adapter' => Env::string('UTILITY_VIEW_ADAPTER', 'plates'),

        // Additional engine‐specific options can go here.
        // For example, if 'twig' is chosen, you might pass $twigOptions in 'extra'.
        'extra' => [
            // 'twigOptions' => ['cache' => __DIR__ . '/../var/cache/twig'],
            // 'logger' => null, // or a LoggerInterface for Twig
        ],
    ],
];
