<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Broadcaster
    |--------------------------------------------------------------------------
    |
    | This option controls the default broadcaster that will be used by the
    | framework when an event needs to be broadcast. You may set this to
    | any of the connections defined in the "connections" array below.
    |
    */

    'default' => env('BROADCAST_DRIVER', 'reverb'),

    /*
    |--------------------------------------------------------------------------
    | Broadcast Connections
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the broadcast connections that will be used
    | to broadcast events to other systems or over websockets. Samples of
    | each available type of connection are provided inside this array.
    |
    */

    'connections' => [

        'reverb' => [
            'driver' => 'reverb',
            'key' => env('REVERB_APP_KEY'),
            'secret' => env('REVERB_APP_SECRET'),
            'app_id' => env('REVERB_APP_ID'),
            'options' => [
                'host' => env('REVERB_HOST', '127.0.0.1'),
                'port' => env('REVERB_PORT', 8080),
                'scheme' => env('REVERB_SCHEME', 'http'),
                'useTLS' => env('REVERB_SCHEME', 'http') === 'https',
            ],
        ],

        'pusher' => [
            'driver' => 'pusher',
            // استخدام Soketi محلي عند عدم وجود إنترنت
            // في حالة وجود إنترنت، يمكن استخدام Pusher الحقيقي
            // القيم الافتراضية: إذا كان BROADCAST_DRIVER=local، استخدم Soketi، وإلا استخدم Pusher
            'key' => env('PUSHER_APP_KEY', env('BROADCAST_DRIVER') === 'local' ? 'local-key' : '86253c368b61504804fa'),
            'secret' => env('PUSHER_APP_SECRET', env('BROADCAST_DRIVER') === 'local' ? 'local-secret' : 'f9fa3b0472b020245ec8'),
            'app_id' => env('PUSHER_APP_ID', env('BROADCAST_DRIVER') === 'local' ? 'local-app' : '2059282'),
            'options' => [
                'cluster' => env('PUSHER_APP_CLUSTER', 'mt1'),
                'useTLS' => (env('PUSHER_SCHEME') ?: (env('BROADCAST_DRIVER') === 'local' ? 'http' : 'https')) === 'https',
                // استخدام Soketi محلي إذا كان BROADCAST_DRIVER=local
                'host' => env('PUSHER_HOST', env('BROADCAST_DRIVER') === 'local' ? '127.0.0.1' : null),
                'port' => (int) env('PUSHER_PORT', env('BROADCAST_DRIVER') === 'local' ? 6001 : 443),
                'scheme' => env('PUSHER_SCHEME', env('BROADCAST_DRIVER') === 'local' ? 'http' : 'https'),
            ],
        ],

        'ably' => [
            'driver' => 'ably',
            'key' => env('ABLY_KEY'),
        ],

        'redis' => [
            'driver' => 'redis',
            'connection' => 'default',
        ],

        'log' => [
            'driver' => 'log',
        ],

        'null' => [
            'driver' => 'null',
        ],

    ],

];
