<?php

namespace Spatie\LaravelEventSauce;

use EventSauce\EventSourcing\AggregateRootId;
use EventSauce\EventSourcing\ConstructingAggregateRootRepository;
use  EventSauce\EventSourcing\MessageDispatcherChain;

class AggregateRootRepository implements \EventSauce\EventSourcing\AggregateRootRepository
{
    /** @var \EventSauce\EventSourcing\ConstructingAggregateRootRepository */
    protected $constructingAggregateRootRepository;

    public function __construct()
    {
        $this->constructingAggregateRootRepository = new ConstructingAggregateRootRepository(
            $this->getAggregateRootClass(),
            app(config('eventsauce.repository')),
            app(MessageDispatcherChain::class)
        );
    }

    public function getAggregateRootClass(): string
    {
        return static::$aggregateRoot;
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
}
