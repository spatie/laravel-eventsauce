<?php

namespace Spatie\LaravelEventSauce\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Spatie\LaravelEventSauce\Exceptions\CouldNotMakeAggregateRoot;

class MakeAggregateRootCommand extends Command
{
    protected $signature = 'make:aggregate-root {class}';

    protected $description = 'Create a new aggregate root class';

    /** @var \Illuminate\Filesystem\Filesystem */
    protected $filesystem;

    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->filesystem = $files;
    }

    public function handle()
    {
        $aggregateRootFqcn = $this->qualifyClass($this->argument('class'));
        $aggregateRootPath = $this->getPath($aggregateRootFqcn);

        $aggregateRootRepositoryFqcn = $this->qualifyClass($this->argument('class').'Repository');
        $aggregateRootRepositoryPath = $this->getPath($aggregateRootRepositoryFqcn);

        $this->ensureValidPaths([$aggregateRootPath, $aggregateRootRepositoryPath]);

        $this->makeDirectory($aggregateRootPath);

        $replacements = [
            'aggregateRootClass' => class_basename($aggregateRootFqcn),
            'namespace' => substr($aggregateRootFqcn, 0, strrpos($aggregateRootFqcn, '\\')),
            'tableName' => snake_case(class_basename($aggregateRootFqcn)).'_domain_messages',
            'migrationClassName' => 'Create'.ucfirst(class_basename($aggregateRootFqcn)).'DomainMessagesTable',
        ];

        $this->filesystem->put($aggregateRootPath, $this->getStubContent('AggregateRoot', $replacements));
        $this->filesystem->put($aggregateRootRepositoryPath, $this->getStubContent('AggregateRootRepository', $replacements));

        $this->createMigration($replacements);

        $this->info('Aggregate root classes and migration created successfully!');
        $this->comment("Run `php artisan migrate` to create the {$replacements['tableName']} table.");
    }

    protected function createMigration(array $replacements)
    {
        $timestamp = now()->format('Y_m_d_His');

        $migrationFileName = "{$timestamp}_create_{$replacements['tableName']}_table.php";
        $this->filesystem->put(database_path("migrations/{$migrationFileName}"), $this->getStubContent('create_domain_messages_table', $replacements));
    }

    protected function getPath(string $name): string
    {
        $name = Str::replaceFirst($this->laravel->getNamespace(), '', $name);

        return $this->laravel['path'].'/'.str_replace('\\', '/', $name).'.php';
    }

    protected function qualifyClass(string $name): string
    {
        $name = ltrim($name, '\\/');

        $rootNamespace = $this->laravel->getNamespace();

        if (Str::startsWith($name, $rootNamespace)) {
            return $name;
        }

        $name = str_replace('/', '\\', $name);

        return $this->qualifyClass(trim($rootNamespace, '\\').'\\'.$name);
    }

    protected function ensureValidPaths(array $paths)
    {
        foreach ($paths as $path) {
            if (file_exists($path)) {
                throw CouldNotMakeAggregateRoot::fileAlreadyExists($path);
            }
        }
    }

    protected function makeDirectory(string $path)
    {
        if (! $this->filesystem->isDirectory(dirname($path))) {
            $this->filesystem->makeDirectory(dirname($path), 0777, true, true);
        }
    }

    protected function getStubContent(string $stubName, array $replacements): string
    {
        $content = $this->filesystem->get(__DIR__."/stubs/{$stubName}.php.stub");

        foreach ($replacements as $search => $replace) {
            $content = str_replace("{{ {$search} }}", $replace, $content);
        }

        return $content;
    }
}
