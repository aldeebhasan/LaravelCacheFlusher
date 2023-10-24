<?php

namespace Aldeebhasan\LaravelSmartCache;

use Illuminate\Cache\Events\KeyWritten;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class LaravelSmartCacheProvider extends ServiceProvider
{
    public function boot()
    {
        Event::listen(
            ['eloquent.saved: *', 'eloquent.created: *'],
            function ($context) {

            }
        );

        Event::listen(KeyWritten::class, function ($context) {

        });
    }

    public function register()
    {
        $this->app->singleton('smart-cache', SmartCacheManager::class);
    }
}
