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

    'meta' => [
        'access_token' => env('META_ACCESS_TOKEN'),
        'accounts' => [
            ['id' => '185496109582193', 'name' => 'Liceu Brasil'],
            ['id' => '689041216147072', 'name' => 'Colégio Itaquá (CONTINGÊNCIA)'],
            ['id' => '2995123880781648', 'name' => 'FISK SUZANO'],
            ['id' => '2168887503468328', 'name' => 'Liceu da Beleza Arujá'],
            ['id' => '1116256633008968', 'name' => 'Colégio Itaquá'],
            ['id' => '3542869715847463', 'name' => 'Colégio DÓ RÉ MI'],
            ['id' => '3854176348214890', 'name' => 'Escola Pequeno Príncipe e Pequena Princesa'],
        ],
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

];
