<?php

namespace Aldeebhasan\LaravelCacheFlusher\Test\Unit;

use Aldeebhasan\LaravelCacheFlusher\CacheFlusherManager;
use Aldeebhasan\LaravelCacheFlusher\Test\TestCase;
use Illuminate\Database\Eloquent\Model;

class CacheFlusherTest extends TestCase
{
    private $cachManager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cachManager = cache()->driver('file');
    }

    public function test_case()
    {

        $manaer = new CacheFlusherManager();
        $manaer->flush();
        $cache = cache()->driver('file');
        $cache->put('store.5', 1);
        $cache->put('store.6', 1);
        $cache->put('mobile.v1.1', 1);
        $cache->put('mobile.v1.2', 1);
        $cache->put('mobile.v2.2', 1);
        event('eloquent.updated: ' . Model::class, \Illuminate\Database\Eloquent\Model::class);
    }
}
