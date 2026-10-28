<?php

return [
    // Times are stored in UTC; this is the owner's working time zone for availability and admin display.
    'timezone' => env('APP_DISPLAY_TIMEZONE', 'Africa/Lagos'),

    'admin_notification_email' => env('ADMIN_NOTIFICATION_EMAIL'),

    'currencies' => ['NGN', 'USD'],
    'default_currency' => 'NGN',

    'payments' => [
        // none | paystack | test (the test gateway refuses to run in production)
        'gateway' => env('PAYMENT_GATEWAY', 'none'),
        'paystack' => [
            'public_key' => env('PAYSTACK_PUBLIC_KEY'),
            'secret_key' => env('PAYSTACK_SECRET_KEY'),
            'base_url' => env('PAYSTACK_BASE_URL', 'https://api.paystack.co'),
        ],
    ],

    'downloads' => [
        'ttl_hours' => (int) env('DOWNLOAD_LINK_TTL_HOURS', 72),
        'max_attempts' => (int) env('DOWNLOAD_MAX_ATTEMPTS', 5),
    ],

    'booking' => [
        'hold_minutes' => (int) env('BOOKING_HOLD_MINUTES', 20),
    ],

    'newsletter' => [
        // none | mailchimp
        'provider' => env('NEWSLETTER_PROVIDER', 'none'),
        'mailchimp' => [
            'api_key' => env('MAILCHIMP_API_KEY'),
            'list_id' => env('MAILCHIMP_LIST_ID'),
            'server_prefix' => env('MAILCHIMP_SERVER_PREFIX'),
        ],
    ],

    'analytics' => [
        'ga_measurement_id' => env('GA_MEASUREMENT_ID'),
    ],
];
