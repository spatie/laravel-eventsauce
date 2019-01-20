<?php

declare(strict_types=1);

namespace Spatie\LaravelEventSauce\Tests;

use EventSauce\LaravelEventSauce\EventSauceServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [EventSauceServiceProvider::class];
    }
}
