<?php

namespace Aldeebhasan\LaravelCacheFlusher;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class CacheFlusherManager
{
    private array $mapping = [];

    private $bindingFn = null;

    private Repository $cacheManager;

    private string $file = 'cache-flusher/cache.json';

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
        $storage = Storage::disk('local');
        if ($storage->exists($this->file)) {
            $data = $storage->get($this->file);

            return json_decode($data) ?? [];
        }

        return [];
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

    public function process(Model $model): void
    {
        $modelClassName = strtolower(class_basename($model));
        $modelClass = get_class($model);
        if ($this->needToCoolDown($modelClassName)) return;

        $keys = $this->getKeys();
        foreach ($this->mapping as $key => $map) {
            if (in_array($modelClass, $map)) {
                $key = $this->handleWithBinding($key, $model);
                $this->handleSingleKey($keys, $key);
                $this->configureCoolDown($modelClassName);
            }
        }
    }

    private function handleWithBinding(string $patten, Model $model): string
    {
        if (!$this->bindingFn || !is_callable($this->bindingFn)) return $patten;

        $matches = [];
        preg_match_all("/{\w+}/", $patten, $matches);
        foreach (array_shift($matches) as $bindKey) {
            $key = str_replace(['{', '}'], '', $bindKey);
            $bindingValue = call_user_func($this->bindingFn, $key, $model);
            if ($bindingValue)
                $patten = str_replace($bindKey, $bindingValue, $patten);
        }

        return $patten;

    }

    public function handleSingleKey(array $keys, string $patten): void
    {
        $matches = preg_grep("/^$patten/i", $keys);
        foreach ($matches as $key) {
            $this->cacheManager->forget($key);
        }
    }

    private function needToCoolDown(string $modelClassName): bool
    {
        $cacheCoolDown = config('cache-flusher.cool-down');

        if (!$cacheCoolDown) return false;
        $invalidatedAt = $this->cacheManager->get("$modelClassName-cooldown");

        if (!$invalidatedAt) return false;

        return now()->diffInSeconds($invalidatedAt) < $cacheCoolDown;

    }

    private function configureCoolDown(string $modelClassName): void
    {
        $cacheCoolDown = config('cache-flusher.cool-down');
        if (!$cacheCoolDown) return;

        $this->cacheManager->put(
            "$modelClassName-cooldown",
            now()->addSeconds($cacheCoolDown)->toDateTimeString()
        );

    }

    public function setBindingFunction(\Closure $closure): void
    {
        $this->bindingFn = $closure;
    }
}
