<?php

namespace Spatie\LaravelEventSauce\Tests;

use Spatie\LaravelEventSauce\Concerns\IgnoresMissingMethods;

class IgnoresMissingMethodsTest extends TestCase
{
    /** @test */
    public function it_will_make_objects_ignore_missing_methods()
    {
        $class = new class {
            use IgnoresMissingMethods;
        };

        $class->nonExistingMethod();

        $this->markTestPassed();
    }

}