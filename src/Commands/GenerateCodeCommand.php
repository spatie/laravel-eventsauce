<?php

namespace Spatie\LaravelEventSauce\Commands;

use Illuminate\Console\Command;
use EventSauce\EventSourcing\CodeGeneration\CodeDumper;
use Spatie\LaravelEventSauce\Exceptions\InvalidConfiguration;
use EventSauce\EventSourcing\CodeGeneration\YamlDefinitionLoader;

class GenerateCodeCommand extends Command
{
    protected $signature = 'eventsauce:generate';

    protected $description = 'Generate EventSauce code.';

    public function handle()
    {
        $this->info('Start generating code...');

        $codeGenerationConfig = config('eventsauce.code_generation');

        collect($codeGenerationConfig)
            ->reject(function (array $config) {
                return is_null($config['input_yaml_file']);
            })
            ->each(function (array $config) {
                $this->generateCode($config['input_yaml_file'], $config['output_file']);
            });

        $this->info('All done!');
    }

    private function generateCode(string $inputFile, string $outputFile)
    {
        if (! file_exists($inputFile)) {
            throw InvalidConfiguration::definitionFileDoesNotExist($inputFile);
        }

        $loader = new YamlDefinitionLoader();
        $dumper = new CodeDumper();

        $loadedYamlContent = $loader->load($inputFile);

        $phpCode = $dumper->dump($loadedYamlContent);

        file_put_contents($outputFile, $phpCode);

        $this->warn("Written code to `{$outputFile}`");
    }
}
