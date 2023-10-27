<?php

namespace Aldeebhasan\LaravelCacheFlusher;

use Aldeebhasan\LaravelCacheFlusher\Facades\CacheFlusher;
use Illuminate\Cache\Events\KeyForgotten;
use Illuminate\Cache\Events\KeyWritten;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class LaravelCacheFlusherProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/cache-flusher.php' => config_path('cache-flusher.php'),
        ], 'cache-flusher');

        $this->registerCacheFlusher();
        CacheFlusher::initialize();
    }

    public function register()
    {

        $this->mergeConfigFrom(
            __DIR__ . '/../config/cache-flusher.php',
            'cache-flusher-config'
        );

        $this->app->singleton('cache-flusher', CacheFlusherManager::class,);
    }

    private function registerCacheFlusher(): void
    {
        if (CacheFlusher::enabled()) {
            Event::listen(
                ['eloquent.updated: *', 'eloquent.created: *', 'eloquent.deleted: *', 'eloquent.saved: *'],
                function (string $event,$model) {
                    $model = last(explode(': ', $event));
                    if (class_exists($model)) {
                        CacheFlusher::process($model);
                    }
                }
            );

            Event::listen(KeyWritten::class, function (KeyWritten $event) {
                CacheFlusher::put($event->key);
            });
            Event::listen(KeyForgotten::class, function (KeyForgotten $event) {
                CacheFlusher::forget($event->key);
            });
        }
    }
}
