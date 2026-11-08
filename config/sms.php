<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default SMS Driver
    |--------------------------------------------------------------------------
    |
    | Supported drivers: "log", "generic_bd", "bulksmsbd", "greenweb"
    |
    */

    'driver' => env('SMS_DRIVER', 'log'),

    'providers' => [

        'generic_bd' => [
            'url' => env('SMS_API_URL', 'https://api.sms-gateway.com/send'),
            'api_key' => env('SMS_API_KEY', ''),
            'sender_id' => env('SMS_SENDER_ID', ''),
        ],

        'bulksmsbd' => [
            'url' => 'https://bulksmsbd.net/api/smsapi',
            'api_key' => env('BULKSMSBD_API_KEY', ''),
            'sender_id' => env('BULKSMSBD_SENDER_ID', ''),
        ],

        'greenweb' => [
            'url' => 'https://api.greenweb.com.bd/api.php',
            'token' => env('GREENWEB_TOKEN', ''),
        ],

    ],

];
