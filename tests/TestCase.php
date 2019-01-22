<?php

namespace Spatie\LaravelEventSauce\Tests;

use CreateStoredEventsTable;
use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\TemporaryDirectory\TemporaryDirectory;
use Spatie\LaravelEventSauce\EventSauceServiceProvider;

class TestCase extends Orchestra
{
    public function setUp()
    {
        parent::setUp();

        $this->setUpDatabase();
    }

    protected function setUpDatabase()
    {
        include_once __DIR__.'/../database/migrations/create_stored_events_table.php.stub';
        (new CreateStoredEventsTable())->up();
    }

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

    protected function markTestPassed()
    {
        $this->assertTrue(true);
    }
}
