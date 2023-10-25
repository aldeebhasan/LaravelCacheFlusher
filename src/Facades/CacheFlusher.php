<?php

namespace Aldeebhasan\LaravelCacheFlusher\Facades;

use Aldeebhasan\LaravelCacheFlusher\CacheFlusherManager;
use Illuminate\Support\Facades\Facade;

/**
 * @method static CacheFlusherManager getItemBasedRecommender()
 * @method static void initialize()
 * @method static bool enabled()
 * @method static void put(string $key)
 * @method static void forget(string|int|array $forgetKey)
 * @method static void flush()
 * @method static void process(string $model)
 * @see CacheFlusherManager
 */
class CacheFlusher extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'cache-flusher';
    }
}
