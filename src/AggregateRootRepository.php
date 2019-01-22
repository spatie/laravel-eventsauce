<?php

namespace Spatie\LaravelEventSauce;

use Exception;
use EventSauce\EventSourcing\Consumer;
use EventSauce\EventSourcing\AggregateRoot;
use EventSauce\EventSourcing\AggregateRootId;
use EventSauce\EventSourcing\MessageDecorator;
use EventSauce\EventSourcing\MessageRepository;
use EventSauce\EventSourcing\MessageDispatcherChain;
use EventSauce\EventSourcing\SynchronousMessageDispatcher;
use EventSauce\EventSourcing\ConstructingAggregateRootRepository;
use EventSauce\EventSourcing\AggregateRootRepository as EventSauceAggregateRootRepository;

abstract class AggregateRootRepository implements EventSauceAggregateRootRepository
{
    /** @var string */
    protected $aggregateRoot = null;

    /** @var array */
    protected $consumers = [];

    /** @var array */
    protected $queuedConsumers = [];

    /** @var string|null */
    protected $messageRepository = null;

    /** @var string|null */
    protected $messageDecorator = null;

    /** @var \EventSauce\EventSourcing\ConstructingAggregateRootRepository */
    protected $constructingAggregateRootRepository;

    public function __construct()
    {
        $aggregateRootClass = $this->getAggregateRootClass();

        if (! is_subclass_of($aggregateRootClass, AggregateRoot::class)) {
            throw new Exception('Not a valid aggregateRoot');
        }

        $this->constructingAggregateRootRepository = new ConstructingAggregateRootRepository(
            $aggregateRootClass,
            $this->getMessageRepository(),
            new MessageDispatcherChain(
                (new QueuedMessageDispatcher())->setJobClass($this->getJobClass())->setConsumers($this->getInstanciatedQueuedConsumers()),
                new SynchronousMessageDispatcher(...$this->getInstanciatedConsumers())
            ),
            $this->getMessageDecorator()
        );
    }

    public function retrieve(AggregateRootId $aggregateRootId): object
    {
        return $this->constructingAggregateRootRepository->retrieve($aggregateRootId);
    }

    public function persist(object $aggregateRoot)
    {
        return $this->constructingAggregateRootRepository->persist($aggregateRoot);
    }

    public function persistEvents(AggregateRootId $aggregateRootId, int $aggregateRootVersion, object ...$events)
    {
        $this->constructingAggregateRootRepository->persistEvents($aggregateRootId, $aggregateRootVersion);
    }

    protected function getAggregateRootClass(): string
    {
        return $this->aggregateRoot;
    }

    public function getJobClass(): string
    {
        return EventSauceJob::class;
    }

    protected function getMessageRepository(): MessageRepository
    {
        $messageRepositoryClass = $this->messageRepository ?? MessageRepository::class;

        return app($messageRepositoryClass);
    }

    protected function getConsumers(): array
    {
        return $this->consumers;
    }

    protected function getQueuedConsumers(): array
    {
        return $this->queuedConsumers;
    }

    protected function getMessageDecorator(): ?MessageDecorator
    {
        return $this->messageDecorator;
    }

    protected function getInstanciatedConsumers(): array
    {
        return $this->instanciate($this->consumers);
    }

    protected function getInstanciatedQueuedConsumers(): array
    {
        return $this->instanciate($this->queuedConsumers);
    }

    protected function instanciate(array $classes): array
    {
        return array_map(function ($class): Consumer {
            return is_string($class)
                ? app($class)
                : $class;
        }, $classes);
    }
}
