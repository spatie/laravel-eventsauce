<?php

namespace Spatie\LaravelEventSauce;

use EventSauce\EventSourcing\Consumer;
use EventSauce\EventSourcing\Header;
use EventSauce\EventSourcing\Message;
use EventSauce\EventSourcing\MessageDispatcher;
use EventSauce\EventSourcing\SynchronousMessageDispatcher;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class QueuedMessageJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable;

    /** @var \EventSauce\EventSourcing\Message[] */
    public $messages = [];

    /** @var string[] */
    public $consumerClasses = [];

    /** @var array */
    public $tags = [];

    public function __construct(array $messages, array $consumers)
    {
        $this->messages = $messages;

        $this->consumerClasses = array_map(function (Consumer $consumer) {
            return get_class($consumer);
        }, $consumers);
    }

    public function tags(): array
    {
        return $this->convertToTags($this->messages);
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

    protected function convertToTags(array $messages): array
    {
        return collect($messages)
            ->flatMap(function (Message $message) {
                return [
                    'aggregateRootId:'.$message->aggregateRootId()->toString(),
                    'aggregateRootType:'.$message->header(Header::AGGREGATE_ROOT_ID_TYPE),
                    'eventType:'.$message->header(Header::EVENT_TYPE),
                ];
            })
            ->filter()
            ->unique()
            ->toArray();
    }
}
