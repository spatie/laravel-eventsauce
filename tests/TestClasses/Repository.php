<?php

namespace Spatie\LaravelEventSauce\Tests\TestClasses;

use Spatie\LaravelEventSauce\AggregateRootRepository;

class Repository extends AggregateRootRepository
{
    protected $aggregateRoot = AggregateRoot::class;

    protected $messageRepository = MyOwnVerySpecialEventStoringModel::class;
}