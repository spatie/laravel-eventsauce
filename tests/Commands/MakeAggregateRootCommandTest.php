<?php

namespace Spatie\LaravelEventSauce\Tests\Commands;

use Illuminate\Filesystem\Filesystem;
use Spatie\LaravelEventSauce\Tests\TestCase;
use Spatie\LaravelEventSauce\Commands\MakeAggregateRootCommand;
use Spatie\LaravelEventSauce\Tests\Mocks\Filesystem as FilesystemMock;

class MakeAggregateRootCommandTest extends TestCase
{
    /** @var \Tests\Mocks\Filesystem */
    protected $filesystem;

    public function setUp()
    {
        parent::setUp();

        $this->filesystem = new FilesystemMock();

        $this->app
            ->when(MakeAggregateRootCommand::class)
            ->needs(Filesystem::class)
            ->give(function () {
                return $this->filesystem;
            });
    }

    /** @test */
    public function it_can_make_an_aggregate_root_and_repository()
    {
        $this->artisan('make:aggregate-root', ['class' => "Directory\Process"]);

        $this->filesystem
            ->assertWrittenTo('laravel/app/Directory/Process.php')
            ->assertWrittenTo('laravel/app/Directory/ProcessRepository.php');
    }
}
