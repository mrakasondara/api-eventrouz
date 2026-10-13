<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'appwrite' => [
        'endpoint' => env('APPWRITE_ENDPOINT'),
        'project_id' => env('APPWRITE_PROJECT_ID'),
        'api_key' => env('APPWRITE_API_KEY'),
        'bucket_id' => env('APPWRITE_BUCKET_ID')
    ],

    'midtrans' => [
        'server_key' => env('MIDTRANS_SERVER_KEY'),
        'is_production' => env('MIDTRANS_IS_PRODUCTION'),
        'is_sanitized' => env('MIDTRANS_IS_SANITIZED'),
        'is_3ds' => env('MIDTRANS_IS_3DS'),
        'override_url' => env('MIDTRANS_OVERRIDE_URL'),
    ]
];
