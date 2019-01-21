<?php

namespace Spatie\LaravelEventSauce\Concerns;

trait IgnoresMissingMethods
{
    public function __call($name, $arguments)
    {
        return;
    }
}