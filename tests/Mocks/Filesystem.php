<?php

namespace Spatie\LaravelEventSauce\Tests\Mocks;

use Illuminate\Filesystem\Filesystem as IlluminateFilesystem;
use Illuminate\Support\Str;
use PHPUnit\Framework\Assert;

class Filesystem extends IlluminateFilesystem
{
    protected $puts = [];

    public function put($path, $contents, $lock = false)
    {
        $relativePath = Str::after($path, getcwd().'/vendor/orchestra/testbench-core/');

        $this->puts[$relativePath] = $contents;
    }

    public function assertWrittenTo($path)
    {
        Assert::assertArrayHasKey($path, $this->puts, "Did not write to `{$path}`");

        return $this;
    }
}
