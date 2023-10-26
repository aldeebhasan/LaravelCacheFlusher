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
        $this->mapping = config('cache-flusher.mapping', []);
        $driver = config('cache-flusher.driver', 'file');
        $this->cacheManager = cache()->driver($driver);
    }

    public function enabled(): bool
    {
        return config('cache-flusher.enabled', false);
    }

    private function getKeys(): array
    {
        return Storage::json('cache-flusher/cache.json') ?? [];
    }

    private function saveKeys($data): void
    {
        Storage::disk('local')->put('cache-flusher/cache.json', json_encode(array_values($data)));
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
        if ($this->needToCoolDown($model)) return;

        $keys = $this->getKeys();
        foreach ($this->mapping as $key => $map) {
            if (in_array($model, $map)) {
                $this->handleSingleKey($keys, $key);
                $this->configureCoolDown($model);
            }
        }
    }

    public function handleSingleKey($keys, $patten): void
    {
        $matches = preg_grep("/^$patten/i", $keys);
        foreach ($matches as $key) {
            $this->cacheManager->forget($key);
        }
    }

    private function needToCoolDown($model): bool
    {
        $modelClassName = last(explode('\\', $model));
        $cacheCooldown = config('cache-flusher.cool-down');
        if (!$cacheCooldown) return false;

        $invalidatedAt = $this->cacheManager->get("$modelClassName-cooldown");
        if (!$invalidatedAt) return false;
        return now()->diffInSeconds($invalidatedAt) < $cacheCooldown;

    }

    private function configureCoolDown($model): void
    {
        $modelClassName = last(explode('\\', $model));
        $cacheCooldown = config('cache-flusher.cool-down');
        if (!$cacheCooldown) return;

        $this->cacheManager->put(
            "$modelClassName-cooldown",
            now()->addSeconds($cacheCooldown)->toDateTimeString()
        );

    }
}
