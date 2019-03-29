<?php

return [
    'db' => [
        'default' => [
            'driver' => 'mysql',
            'host' => 'localhost',
            'dbname' => 'standard',
            'user' => 'standard',
            'password' => 'Password00',
        ],
    ],
    'auth' => [
        'expire' => 600 // 25 min
    ],
    'extensions' => [
        'jquery' => [
            'location' => 'local',
            'ui' => true,
        ],

        'bootstrap' => [
            'location' => 'local',
            'theme' => '',
        ],

        'ckeditor' => [
            'location' => 'local',
            'autoload' => false,
        ],

        'ckfinder' => [
            'autoload' => false,
        ],

        'jstree' => [
            'autoload' => false,
        ]
    ],

    'errors' => [
    403 => '///403',
    401 => '///401'
    ]
];