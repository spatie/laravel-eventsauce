<?php

namespace Spatie\LaravelEventSauce;

use EventSauce\EventSourcing\Message;
use EventSauce\EventSourcing\Consumer;
use EventSauce\EventSourcing\MessageDispatcher as EventSauceMessageDispatcher;

class QueuedMessageDispatcher implements EventSauceMessageDispatcher
{
    /** @var  string */
    protected $jobClass;

    /** @var \EventSauce\EventSourcing\Consumer[] */
    protected $consumers;

    public function setJobClass(string $jobClass)
    {
        $this->jobClass = $jobClass;

        return $this;
    }

    public function setConsumers(array $consumers)
    {
        $this->consumers = $consumers;

        return $this;
    }

    public function dispatch(Message ...$messages)
    {
        if (! count($this->consumers)) {
            return;
        }

        dispatch(new $this->jobClass($messages, $this->consumers));
    }
}
