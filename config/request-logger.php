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
            config('session.cookie'),
            // Session cookies set by whatever sits in front of the
            // application (auth proxies and the like). Not ours to log, and
            // their raw value is not necessarily valid UTF-8.
            '*_session'
        ],
        'post' => [
            '_token',
            'password'
        ]
    ]
];
