<?php

namespace Spatie\LaravelEventSauce\Exceptions;

use Exception;

class CouldNotMakeAggregateRoot extends Exception
{

    public static function fileAlreadyExists(string $path): self
    {
        return new static("Could not create a file at path `{$path}` because it already existst.");
    }

}