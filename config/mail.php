<?php

return [

    'default' => env('MAIL_MAILER', 'log'),

    'mailers' => [

        'smtp' => [
            'transport' => 'smtp',
            'url' => env('MAIL_URL'),
            'host' => env('MAIL_HOST', '127.0.0.1'),
            'port' => env('MAIL_PORT', 2525),
            'encryption' => env('MAIL_ENCRYPTION', 'tls'),
            'username' => env('MAIL_USERNAME'),
            'password' => env('MAIL_PASSWORD'),
            'timeout' => null,
            'local_domain' => env('MAIL_EHLO_DOMAIN'),
        ],

        // ✅ START: Updated Gmail Mailer Configurations
        'gmail_1' => [
            'transport' => 'smtp',
            'host' => 'smtp.gmail.com',
            'port' => 465,
            'encryption' => 'ssl',
            'username' => env('GMAIL_1_USERNAME'),
            'password' => env('GMAIL_1_PASSWORD'),
            'timeout' => null,
            'from' => ['address' => env('GMAIL_1_USERNAME'), 'name' => env('GMAIL_1_FROM_NAME')],
        ],
        'gmail_2' => [
            'transport' => 'smtp',
            'host' => 'smtp.gmail.com',
            'port' => 465,
            'encryption' => 'ssl',
            'username' => env('GMAIL_2_USERNAME'),
            'password' => env('GMAIL_2_PASSWORD'),
            'timeout' => null,
            'from' => ['address' => env('GMAIL_2_USERNAME'), 'name' => env('GMAIL_2_FROM_NAME')],
        ],
        'gmail_3' => [
            'transport' => 'smtp',
            'host' => 'smtp.gmail.com',
            'port' => 465,
            'encryption' => 'ssl',
            'username' => env('GMAIL_3_USERNAME'),
            'password' => env('GMAIL_3_PASSWORD'),
            'timeout' => null,
            'from' => ['address' => env('GMAIL_3_USERNAME'), 'name' => env('GMAIL_3_FROM_NAME')],
        ],
        // ✅ END: Updated Gmail Mailer Configurations

        // ... rest of the file is the same
        'ses' => [ 'transport' => 'ses', ],
        'postmark' => [ 'transport' => 'postmark', ],
        'sendmail' => [ 'transport' => 'sendmail', 'path' => env('MAIL_SENDMAIL_PATH', '/usr/sbin/sendmail -bs -i'), ],
        'log' => [ 'transport' => 'log', 'channel' => env('MAIL_LOG_CHANNEL'), ],
        'array' => [ 'transport' => 'array', ],
        'failover' => [ 'transport' => 'failover', 'mailers' => [ 'smtp', 'log', ], ],
    ],

    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
        'name' => env('MAIL_FROM_NAME', 'Example'),
    ],

];