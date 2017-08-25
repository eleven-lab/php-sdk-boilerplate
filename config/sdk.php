<?php

return [
    # Available modes: sandbox, live
    'mode'          => 'sandbox',
    'api_version'   => 'v1',
    'timeout'       => 20,
    'cache_driver'  => 'file',
    'verify_callbacks' => true,
    'lang' => [
        'resource' => __DIR__.'/../lang',
        'locale' => 'en'
    ],

    'live'      => [
        'base_url'      => 'https://example.com/api',
        'credentials' => [
            'user' => 'user',
            'password' => 'password'
        ]
    ],

    'sandbox'   => [
        'base_url'       => 'https://sandbox.example.com/api',
        'credentials' => [
            'user' => 'user',
            'password' => 'password'
        ]
    ]
];