<?php

namespace Aldeebhasan\LaravelCacheFlusher\Test\Unit;

use Aldeebhasan\LaravelCacheFlusher\CacheFlusherManager;
use Aldeebhasan\LaravelCacheFlusher\Facades\CacheFlusher;
use Aldeebhasan\LaravelCacheFlusher\Test\TestCase;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\WithFaker;

class CacheFlusherTest extends TestCase
{
    use WithFaker;

    private Repository $cacheManager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cacheManager = cache()->driver('array');
    }

    private function setCustomConfig(array $config): void
    {
        $config = array_merge(config('cache-flusher'), $config);
        config()->set('cache-flusher', $config);
        CacheFlusher::initialize();
        CacheFlusher::flush();
    }

    private function initKeys(array $keys): void
    {
        foreach ($keys as $key) {
            $this->cacheManager->put($key, $this->faker->randomKey);
        }
    }

    private function getStoredKeys(): array
    {
        $object = app('cache-flusher');
        $reflector = new \ReflectionObject($object);
        $method = $reflector->getMethod('getKeys');
        $method->setAccessible(true);
        return $method->invoke($object);
    }

    public function test_keys_store_correctly_in_manager()
    {
        $this->setCustomConfig([]);
        $keys = ['test', 'test.1', 'test.2', 'test_2.v1.1', 'test_2.v2.1'];
        $this->initKeys($keys);

        $storeKeys = $this->getStoredKeys();

        self::assertEquals($keys, $storeKeys);

    }

    public function test_keys_removed_correctly_from_manager()
    {
        $this->setCustomConfig([]);
        $keys = ['test', 'test.1'];
        $this->initKeys(['test', 'test.1']);

        $storeKeys = $this->getStoredKeys();
        self::assertEquals($keys, $storeKeys);

        $this->cacheManager->forget('test');
        $storeKeys = $this->getStoredKeys();
        self::assertEquals(['test.1'], $storeKeys);
    }

    public function test_flush_keys_after_model_create()
    {
        $this->setCustomConfig([
            'mapping' => [
                '(test\..+|test_2\.v1\.*)' => [Model::class],
            ]
        ]);

        $keys = ['test', 'test.1', 'test.2', 'test_2.v1.1', 'test_2.v2.1'];
        $this->initKeys($keys);

        event('eloquent.created: ' . Model::class, Model::class);

        $storeKeys = $this->getStoredKeys();
        self::assertEquals(['test', 'test_2.v2.1'], $storeKeys);
    }
}
