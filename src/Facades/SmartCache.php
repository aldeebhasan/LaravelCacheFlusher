<?php

namespace Aldeebhasan\LaravelSmartCache\Facades;

use Aldeebhasan\LaravelSmartCache\SmartCacheManager;
use Illuminate\Support\Facades\Facade;

/**
 * @method static SmartCacheManager getItemBasedRecommender()
 * @see SmartCacheManager
 */
class SmartCache extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'smart-cache';
    }
}
