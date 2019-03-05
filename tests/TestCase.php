<?php

namespace Spatie\LaravelEventSauce\Tests;

use CreateDomainMessagesTable;
use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\TemporaryDirectory\TemporaryDirectory;
use Spatie\LaravelEventSauce\EventSauceServiceProvider;

class TestCase extends Orchestra
{
    public function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase();
    }

    protected function setUpDatabase()
    {
        if (! class_exists('CreateDomainMessagesTable')) {
            $contents = file_get_contents(__DIR__.'/../src/Commands/stubs/create_domain_messages_table.php.stub');

            $contents = str_replace('<?php', '', $contents);
            $contents = str_replace('{{ migrationClassName }}', 'CreateDomainMessagesTable', $contents);
            $migrationCode = str_replace('{{ tableName }}', 'domain_messages', $contents);
            eval($migrationCode);
        }

        (new CreateDomainMessagesTable())->up();
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
