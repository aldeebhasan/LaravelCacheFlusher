<?php

return [

    'enabled' => env('CACHE_FLUSHER_ENABLED', false),
    'driver' => env('CACHE_FLUSHER_DRIVER', env('CACHE_DRIVER', 'file')), // array|database|apc|redis|memcached
    'cool-down' => env('CACHE_FLUSHER_COOL_DOWN'),

    /*add your mapping models here
      example:
        // to remove `store_info` cache when any of the models changed
        'store_info'  =>  [ Product::class, Category::class ]
        // to remove all the keys started with `store_movie.v1.`  when any of the models changed
        'store_movie.v1\.*'  =>  [ Product::class, Category::class ]
         // to remove all the keys that match the regex provided
         // like: company.1.store.products, company.2.store.categories ...
        'company\.*\.store\.*'  =>  [ Product::class, Category::class ]
    */
    'mapping' => [

    ],

];
