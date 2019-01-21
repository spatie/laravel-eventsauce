<?php

namespace EventSauce\LaravelEventSauce\Tests\Commands;

use Spatie\LaravelEventSauce\Tests\TestCase;
use Spatie\LaravelEventSauce\Exceptions\InvalidConfiguration;

class GenerateCodeCommandTest extends TestCase
{
    /** @test */
    public function it_can_generate_code_starting_from_a_yaml_file()
    {
        $outputFile = $this->getTemporaryDirectory()->path('generated-code.php');

        $this->assertFileNotExists($outputFile);

        config()->set('eventsauce.code_generation', [
            ['input_yaml_file' => $this->getStubPath('commands-and-events.yml'), 'output_file' => $outputFile],
        ]);

        $this->artisan('eventsauce:generate')->assertExitCode(0);

        $this->assertFileExists($outputFile);
        $this->assertStringStartsWith('<?php', file_get_contents($outputFile));
    }

    /** @test */
    public function it_will_throw_an_exception_if_the_input_yaml_file_does_not_exist()
    {
        $outputFile = $this->getTemporaryDirectory()->path('generated-code.php');

        config()->set('eventsauce.code_generation', [
            ['input_yaml_file' => 'non-existing-input', 'output_file' => $outputFile],
        ]);

        $this->expectException(InvalidConfiguration::class);

        $this->artisan('eventsauce:generate');
    }
}
