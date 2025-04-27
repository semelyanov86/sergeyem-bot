<?php

declare(strict_types=1);

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
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],
    'linkace' => [
        'token' => env('LINKACE_TOKEN'),
        'url' => 'https://links.sergeyem.ru/api/v2',
    ],
    'easylist' => [
        'token' => env('EASYLIST_TOKEN'),
        'url' => 'https://easylist.sergeyem.ru/api/v1/',
    ],
    'easywords' => [
        'url' => 'https://easywordsapp.ru/api',
        'token' => env('EASYWORDS_TOKEN'),
    ],

    'firefly' => [
        'server' => env('FIREFLY_SERVER', 'https://finance.sergeyem.ru/api/v1'),
        'token' => env('FIREFLY_TOKEN'),
    ],
    'checker' => [
        'websites' => [
            'https://sergeyem.ru' => 'WEB-разработчик, внедрение CRM. Программист Laravel',
            'https://sergeyem.eu' => 'Ein erfahrener Webentwickler',
            'https://cloud.sergeyem.ru/index.php/login' => '<a href="https://owncloud.com" target="_blank" rel="noreferrer">ownCloud</a>',
            'https://keys.sergeyem.ru:8443/#/login' => '<i class="bwi bwi-spinner bwi-spin bwi-3x tw-text-muted" title="Loading" aria-hidden="true"></i>',
            'https://itvolga.com' => '+7(8352)22-36-06',
            'https://creditcoop.ru' => 'Кредитная кооперация Чувашии: кредитные кооперативы и союзы',
            'https://mautic.sergeyem.ru/s/login' => 'keep me logged in',
            'https://mautic.itvolga.com/s/login' => 'keep me logged in',
            'https://links.sergeyem.ru/login' => 'Forgot your password?',
            'https://easywordsapp.ru/#/login' => '<link rel="stylesheet" href="https://easywordsapp.ru/build/assets',
        ],
    ],
    'currency' => [
        'eur_url' => env('CURRENCY_RATE_URL'),
        'eur_key' => env('CURRENCY_RATE_KEY'),
        'cbr_url' => 'https://www.cbr.ru/scripts/XML_daily.asp',
    ],
];
