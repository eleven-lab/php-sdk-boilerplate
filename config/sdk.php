<?php

return [
    # Available modes: sandbox, live
    'mode'          => 'sandbox',
    'api_version'   => null,
    'timeout'       => 20,
    'cache_driver'  => 'file',
    'verify_callbacks' => true,
    'lang' => [
        'resource' => __DIR__.'/../lang',
        'locale' => 'en'
    ],

    'live'      => [
        'base_url'      => 'https://my-json-server.typicode.com/eleven-lab/php-sdk-boilerplate',
        'credentials' => [
            'user' => 'user',
            'password' => 'password'
        ]
    ],

    'sandbox'   => [
        'base_url'       => 'https://my-json-server.typicode.com/eleven-lab/php-sdk-boilerplate',
        'credentials' => [
            'client_id' => 'user',
            'secret' => 'password'
        ]
    ]
];