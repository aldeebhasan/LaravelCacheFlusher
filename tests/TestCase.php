<?php

namespace Aldeebhasan\LaravelCacheFlusher\Test;

use Aldeebhasan\LaravelCacheFlusher\LaravelCacheFlusherProvider;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app)
    {
        return [LaravelCacheFlusherProvider::class];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('cache-flusher.enabled', true);

    }
}
