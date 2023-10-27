<?php

namespace Aldeebhasan\LaravelCacheFlusher\Facades;

use Aldeebhasan\LaravelCacheFlusher\CacheFlusherManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Facade;

/**
 * @method static void initialize()
 * @method static bool enabled()
 * @method static void put(string $key)
 * @method static void forget(string|int|array $forgetKey)
 * @method static void flush()
 * @method static void process(Model $model)
 * @method static void setBindingFunction(\Closure $closure)
 * @see CacheFlusherManager
 */
class CacheFlusher extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'cache-flusher';
    }
}
