<?php

namespace Aldeebhasan\LaravelCacheFlusher;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Storage;

class CacheFlusherManager
{
    private array $mapping = [];

    private Repository $cacheManager;

    public function initialize(): void
    {
        $this->mapping = config('smart-cache.mapping', []);
        $driver = config('smart-cache.driver', 'file');
        $this->cacheManager = cache()->driver($driver);

    }

    public function enabled(): bool
    {
        return config('smart-cache.enabled', false);
    }

    private function getKeys(): array
    {
        return Storage::json('smart-cache/cache.json') ?? [];
    }

    private function saveKeys($data): void
    {
        Storage::disk('local')->put('smart-cache/cache.json', json_encode($data));
    }

    public function put(string $key): void
    {
        $keys = $this->getKeys();
        if (!in_array($key, $keys)) {
            $keys[] = $key;
            $this->saveKeys($keys);
        }
    }

    public function forget(string|int|array $forgetKey): void
    {
        $keys = $this->getKeys();
        foreach ((array)$forgetKey as $key) {
            if (($key = array_search($key, $keys)) !== false) {
                unset($keys[$key]);
            }
        }
        $this->saveKeys($keys);
    }

    public function flush(): void
    {
        $this->saveKeys([]);
    }

    public function process(string $model): void
    {
        $keys = $this->getKeys();
        foreach ($this->mapping as $key => $map) {
            if (in_array($model, $map)) {
                $this->processSingle($keys, $key);
            }
        }
    }

    private function processSingle($keys, $patten): void
    {
        $matches = preg_grep("/^$patten/i", $keys);
        foreach ($matches as $key) {
            $this->cacheManager->forget($key);
        }
    }
}
