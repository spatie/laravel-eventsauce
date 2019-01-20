<?php

namespace Spatie\LaravelEventSauce;

use Illuminate\Bus\Queueable;
use EventSauce\EventSourcing\Message;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use EventSauce\EventSourcing\MessageDispatcher;

class EventSauceJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable;

    /** @var \EventSauce\EventSourcing\Message[] */
    protected $messages = [];

    public function __construct(Message ...$messages)
    {
        $this->messages = $messages;
    }

    public function handle(MessageDispatcher $dispatcher): void
    {
        $dispatcher->dispatch(...$this->messages);
    }
}
