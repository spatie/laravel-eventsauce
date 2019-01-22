<?php

namespace Spatie\LaravelEventSauce\Tests\TestClasses;

use EventSauce\EventSourcing\AggregateRoot as EventSauceAggregateRoot;
use EventSauce\EventSourcing\AggregateRootBehaviour;
use Spatie\LaravelEventSauce\Concerns\IgnoresMissingMethods;

class AggregateRoot implements EventSauceAggregateRoot
{
    use AggregateRootBehaviour,
        IgnoresMissingMethods;

    public function testEvent($number = 1)
    {
        $this->recordThat(new TestEvent($number));
    }
}
