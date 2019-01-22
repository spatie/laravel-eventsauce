<?php

namespace Spatie\LaravelEventSauce\Commands;

use Illuminate\Console\Command;
use EventSauce\EventSourcing\CodeGeneration\CodeDumper;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use ReflectionClass;
use Spatie\LaravelEventSauce\Exceptions\CouldNotMakeAggregateRoot;
use Spatie\LaravelEventSauce\Exceptions\InvalidConfiguration;
use EventSauce\EventSourcing\CodeGeneration\YamlDefinitionLoader;

class MakeAggregateRootCommand extends Command
{
    protected $signature = 'make:aggregate-root {class}';

    protected $description = 'Create a new aggregate root class';

    /** @var \Illuminate\Filesystem\Filesystem*/
    protected $files;

    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->files = $files;
    }

    public function handle()
    {
        $aggregateRootFqcn = $this->qualifyClass($this->argument('class'));
        $aggregateRootPath = $this->getPath($aggregateRootFqcn);

        $aggregateRootRepositoryFqcn = $this->qualifyClass($this->argument('class') . 'Repository');
        $aggregateRootRepositoryPath = $this->getPath($aggregateRootRepositoryFqcn);

        $this->ensureValidPaths([$aggregateRootPath, $aggregateRootRepositoryPath]);

        $this->makeDirectory($aggregateRootPath);

        $replacements = [
            'aggregateRootClass' => class_basename($aggregateRootFqcn),
            'namespace' => substr($aggregateRootFqcn, 0, strrpos( $aggregateRootFqcn, '\\')),
        ];

        $this->files->put($aggregateRootPath, $this->getClassContent('AggregateRoot', $replacements));
        $this->files->put($aggregateRootRepositoryPath, $this->getClassContent('AggregateRootRepository', $replacements));

        $this->info('Aggregate root created successfully!');
    }

    protected function getPath($name)
    {
        $name = Str::replaceFirst($this->rootNamespace(), '', $name);

        return $this->laravel['path'] . '/' . str_replace('\\', '/', $name) . '.php';
    }

    /**
     * Parse the class name and format according to the root namespace.
     *
     * @param  string $name
     *
     * @return string
     */
    protected function qualifyClass($name)
    {
        $name = ltrim($name, '\\/');

        $rootNamespace = $this->laravel->getNamespace();

        if (Str::startsWith($name, $rootNamespace)) {
            return $name;
        }

        $name = str_replace('/', '\\', $name);

        return $this->qualifyClass(trim($rootNamespace, '\\') . '\\' . $name);
    }

    protected function ensureValidPaths(array $paths)
    {
        foreach ($paths as $path) {
            if (file_exists($path)) {
                throw CouldNotMakeAggregateRoot::fileAlreadyExists($path);
            };
        }
    }

    protected function makeDirectory($path)
    {
        if (! $this->files->isDirectory(dirname($path))) {
            $this->files->makeDirectory(dirname($path), 0777, true, true);
        }

        return $path;
    }

    protected function getClassContent(string $stubName, array $replacements)
    {
        $content = $this->files->get(__DIR__ . "/stubs/{$stubName}.php.stub");

        foreach($replacements as $search => $replace)
        {
            $content = str_replace("{{ {$search} }}", $replace, $content);
        }

        return $content;
    }
}
