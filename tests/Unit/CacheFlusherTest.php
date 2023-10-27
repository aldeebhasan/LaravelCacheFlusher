<?php

namespace Aldeebhasan\LaravelCacheFlusher\Test\Unit;

use Aldeebhasan\LaravelCacheFlusher\CacheFlusherManager;
use Aldeebhasan\LaravelCacheFlusher\Facades\CacheFlusher;
use Aldeebhasan\LaravelCacheFlusher\Test\TestCase;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery\MockInterface;

class CacheFlusherTest extends TestCase
{
    use WithFaker;

    private Repository $cacheManager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cacheManager = cache()->driver('file');
    }

    private function setCustomConfig(array $config): void
    {
        $config = array_merge(config('cache-flusher'), $config);
        config()->set('cache-flusher', $config);
        CacheFlusher::initialize();
        CacheFlusher::flush();
        $this->cacheManager->clear();
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
                '(test\..+|test_2\.v1\.*)' => [User::class],
            ]
        ]);

        $keys = ['test', 'test.1', 'test.2', 'test_2.v1.1', 'test_2.v2.1'];
        $this->initKeys($keys);

        event('eloquent.created: ' . User::class, new User);

        $storeKeys = $this->getStoredKeys();
        self::assertEquals(['test', 'test_2.v2.1'], $storeKeys);
    }

    public function test_check_processes_of_multi_model_create()
    {
        $this->setCustomConfig([
            'mapping' => [
                '(test\..+|test_2\.v1\.*)' => [User::class],
            ],
            'cool-down' => '5'
        ]);


        CacheFlusher::shouldReceive('process')->twice();
        event('eloquent.created: ' . User::class, new User);
        event('eloquent.created: ' . User::class, new User);

    }

    public function test_cool_down_after_multi_model_create()
    {
        $this->setCustomConfig([
            'mapping' => [
                '(test\..+|test_2\.v1\.*)' => [User::class],
            ],
            'cool-down' => '5'
        ]);

        $mock = $this->partialMock(
            CacheFlusherManager::class,
            function (MockInterface $mock) {
                $mock->shouldReceive('handleSingleKey')->once();
            });
        $mock->initialize();
        $mock->process(new User);
        $mock->process(new User);
    }

    public function test_cool_down_flush()
    {
        $this->setCustomConfig([
            'mapping' => [
                '(test\..+|test_2\.v1\.*)' => [User::class],
            ],
            'cool-down' => '1'
        ]);

        $mock = $this->partialMock(
            CacheFlusherManager::class,
            function (MockInterface $mock) {
                $mock->shouldReceive('handleSingleKey')->twice();
            });
        $mock->initialize();
        $mock->process(new User);
        $mock->process(new User);

        sleep(2);
        $mock->process(new User);
    }

    public function test_auto_binding()
    {

        $this->setCustomConfig([
            'mapping' => [
                '^companies\.{company_id}\.stores' => [User::class],
                '^companies\.{company_id}\.mobiles\.{user_id}' => [User::class],
            ]
        ]);
        CacheFlusher::setBindingFunction(
            function (string $bindingKey, Model $model): ?string {
                switch ($bindingKey) {
                    case "company_id":
                        return '1'; //$model->company_id
                    case "user_id":
                        if ($model instanceof User)
                            return "2"; // $model->user_id;
                        break;
                }
                return null;
            });

        $keys = ['companies.1.stores', 'companies.2.stores', 'companies.1.mobiles', 'companies.1.mobiles.2.products'];
        $this->initKeys($keys);

        $user = tap(new User(), fn($user) => $user->company_id = 1);
        event('eloquent.created: ' . User::class, $user);

        $storeKeys = $this->getStoredKeys();
        self::assertEquals(['companies.2.stores', 'companies.1.mobiles'], $storeKeys);
    }

}
