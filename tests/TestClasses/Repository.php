<?php

namespace Spatie\LaravelEventSauce\Tests\TestClasses;

use Spatie\LaravelEventSauce\AggregateRootRepository;

/** @method \Spatie\LaravelEventSauce\Tests\TestClasses\AggregateRoot retrieve */
class Repository extends AggregateRootRepository
{
    protected $aggregateRoot = AggregateRoot::class;
}
