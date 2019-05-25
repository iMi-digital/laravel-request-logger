<?php

return [
    'except' => [
        'uri' => [

        ],
        'get' => [
            //
        ],
        'cookies' => [
            'XSRF-TOKEN',
            config('session.cookie')
        ],
        'post' => [
            '_token',
            'password'
        ]
    ]
];
