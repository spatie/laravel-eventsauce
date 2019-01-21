<?php

namespace Spatie\LaravelEventSauce;

use EventSauce\EventSourcing\Consumer;
use EventSauce\EventSourcing\Message;
use EventSauce\EventSourcing\MessageDispatcher as EventSauceMessageDispatcher;

class QueuedMessageDispatcher implements EventSauceMessageDispatcher
{
    /** @var \EventSauce\EventSourcing\Consumer[]  */
    protected $consumers;

    public function __construct(Consumer ...$consumers)
    {
        $this->consumers = $consumers;
    }

    public function dispatch(Message ...$messages)
    {
        dispatch(new EventSauceJob($messages, $this->consumers));
    }
}
