<?php declare(strict_types=1);

use BitSynama\Lapis\Framework\Foundation\Env;

return [
    // MailService might be implemented by one of several providers.
    'mail' => [
        'provider' => Env::string('SERVICE_MAIL_PROVIDER', 'smtp'),
        'default_from' => Env::string('SERVICE_MAIL_DEFAULT_FROM', 'noreply@bitsynama.local'),
        'default_from_name' => Env::string('SERVICE_MAIL_DEFAULT_FROM_NAME', 'Lapis Orkestra'),
        'smtp' => [
            'host' => Env::string('SERVICE_MAIL_SMTP_HOST', 'localhost'),
            'username' => Env::string('SERVICE_MAIL_SMTP_USERNAME', ''),
            'password' => Env::string('SERVICE_MAIL_SMTP_PASSWORD', ''),
            'port' => Env::int('SERVICE_MAIL_SMTP_PORT', 1025),
            'encryption' => Env::string('SERVICE_MAIL_SMTP_ENCRYPTION', 'tls'),
            'authenticate' => Env::bool('SERVICE_MAIL_SMTP_AUTHENTICATE', false),
        ],
        'sendgrid' => [
            'api_key' => Env::string('SERVICE_MAIL_SENDGRID_API_KEY'),
        ],
    ],

    // Queue / job‐dispatcher
    'queue' => [
        'driver' => Env::string('SERVICE_QUEUE_DRIVER', 'sync'),
        'redis' => [
            'host' => Env::string('SERVICE_QUEUE_REDIS_HOST', '127.0.0.1'),
            'port' => Env::int('SERVICE_QUEUE_REDIS_PORT', 6379),
            'db' => 0,
        ],
    ],

    'sms' => [
        'provider' => Env::string('SERVICE_SMS_PROVIDER', 'd7'),
        'd7' => [
            'api_key' => Env::string('SERVICE_SMS_D7_API_KEY', ''),
            'sender_id' => 'VERIFY', // Optional: shown to user
        ],
        'messagebird' => [
            'access_key' => Env::string('SERVICE_MESSAGEBIRD_KEY'),
            'sender' => Env::string('SERVICE_SMS_SENDER_NAME', 'Lapis Orkestra'),
        ],
        'twilio' => [
            'sid' => Env::string('SERVICE_SMS_TWILIO_SID'),
            'token' => Env::string('SERVICE_SMS_TWILIO_TOKEN'),
            'from' => Env::string('SERVICE_SMS_TWILIO_FROM', 'Lapis Orkestra'),
        ],
    ],
];
