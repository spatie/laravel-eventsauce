<?php

namespace Spatie\LaravelEventSauce;

use EventSauce\EventSourcing\Message;
use EventSauce\EventSourcing\MessageDispatcher as EventSauceMessageDispatcher;

class MessageDispatcher implements EventSauceMessageDispatcher
{
    public function dispatch(Message ...$messages)
    {
        foreach ($messages as $message) {
            dispatch(new EventSauceJob($message));
        }
    }
}
