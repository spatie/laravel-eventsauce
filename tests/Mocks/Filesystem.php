<?php

namespace Spatie\LaravelEventSauce\Tests\Mocks;

use Illuminate\Filesystem\Filesystem as IlluminateFilesystem;
use PHPUnit\Framework\Assert;

class Filesystem extends IlluminateFilesystem
{
    protected $puts = [];

    public function put($path, $contents, $lock = false)
    {
        $relativePath = str_after($path, 'Concerns/../../');

        $this->puts[$relativePath] = $contents;
    }

    public function assertWrittenTo($path)
    {
        Assert::assertArrayHasKey($path, $this->puts, "Did not write to `{$path}`");

        return $this;
    }
}