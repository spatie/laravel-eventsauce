<?php

namespace Spatie\LaravelEventSauce;

use EventSauce\EventSourcing\SynchronousMessageDispatcher;
use Illuminate\Bus\Queueable;
use EventSauce\EventSourcing\Consumer;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use EventSauce\EventSourcing\MessageDispatcher;

class EventSauceJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable;

    /** @var \EventSauce\EventSourcing\Message[] */
    protected $messages = [];

    /** @var string[] */
    protected $consumerClasses = [];

    public function __construct(array $messages, array $consumers)
    {
        $this->messages = $messages;

        $this->consumerClasses = array_map(function (Consumer $consumer) {
            return get_class($consumer);
        }, $consumers);
    }

    public function handle()
    {
        $dispatcher = $this->getMessageDispatcher();

        $dispatcher->dispatch(...$this->messages);
    }

    public function getMessageDispatcher(): MessageDispatcher
    {
        $consumers = collect($this->consumerClasses)
            ->filter(function (string $consumerClass) {
                return class_exists($consumerClass);
            })
            ->map(function (string $consumerClass) {
                return app($consumerClass);
            })
            ->toArray();

        return new SynchronousMessageDispatcher(...$consumers);
    }
}
