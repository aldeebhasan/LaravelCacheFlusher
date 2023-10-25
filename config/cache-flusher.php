<?php

return [

    'enabled' => env('CACHE_FLUSHER_ENABLED', false),
    'driver' => env('CACHE_FLUSHER_DRIVER', env('CACHE_DRIVER', 'file')), // array|database|apc|redis|memcached

    'mapping' => [
        '(store\.*|mobile\.v1\.*)' => [
            \Illuminate\Database\Eloquent\Model::class,
        ],
        'mobile\.v2\.*' => [
            \Illuminate\Database\Eloquent\Collection::class,
        ],

    ],

];
