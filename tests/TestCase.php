<?php

declare(strict_types=1);

namespace Spatie\LaravelEventSauce\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\TemporaryDirectory\TemporaryDirectory;
use Spatie\LaravelEventSauce\EventSauceServiceProvider;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [EventSauceServiceProvider::class];
    }

    public function getTemporaryDirectory(): TemporaryDirectory
    {
        return (new TemporaryDirectory('tests/temp'))->force()->empty();
    }

    protected function getStubPath(string $path): string
    {
        return __DIR__."/stubs/{$path}";
    }
}
