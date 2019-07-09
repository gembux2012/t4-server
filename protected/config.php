<?php

return [
    'db' => [
        'default' => [
            'driver' => 'mysql',
            'host' => 'localhost',
            'dbname' => 'standard',
            'user' => 'root',
            'password' => 'Password00',
        ],
    ],
    'auth' => [
        'expire' => 6000 // 25 min
    ],
    'extensions' => [
        'jquery' => [
            'location' => 'local',
            'ui' => false,
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