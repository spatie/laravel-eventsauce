<?php

namespace Spatie\LaravelEventSauce\Tests;

use EventSauce\EventSourcing\Message;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Spatie\LaravelEventSauce\QueuedMessageJob;
use Spatie\LaravelEventSauce\Tests\TestClasses\AggregateRoot;
use Spatie\LaravelEventSauce\Tests\TestClasses\AlternativeAggregateRoot;
use Spatie\LaravelEventSauce\Tests\TestClasses\AlternativeQueuedMessageJob;
use Spatie\LaravelEventSauce\Tests\TestClasses\Identifier;
use Spatie\LaravelEventSauce\Tests\TestClasses\Repository;
use Spatie\LaravelEventSauce\Tests\TestClasses\TestConsumer;
use Spatie\LaravelEventSauce\Tests\TestClasses\TestEvent;

class AggregateRootRepositoryTest extends TestCase
{
    /** @test */
    public function it_can_construct_an_aggregate_root()
    {
        $repository = new Repository();

        $aggregateRoot = $repository->retrieve(new Identifier('1'));

        $this->assertInstanceOf(AggregateRoot::class, $aggregateRoot);
    }

    /** @test */
    public function it_can_construct_an_alternative_aggregate_root()
    {
        $repository = new class() extends Repository {
            protected $aggregateRoot = AlternativeAggregateRoot::class;
        };

        $aggregateRoot = $repository->retrieve(new Identifier('1'));

        $this->assertInstanceOf(AlternativeAggregateRoot::class, $aggregateRoot);
    }

    /** @test */
    public function it_can_record_an_event()
    {
        $repository = new Repository();

        $this->recordTestEvent($repository);

        $this->assertCount(1, DB::table('domain_messages')->get());
    }

    /** @test */
    public function it_can_use_an_alternative_table_name()
    {
        DB::select('CREATE TABLE `other_domain_messages` AS SELECT * FROM `domain_messages` WHERE 0');

        $repository = new class() extends Repository {
            protected $tableName = 'other_domain_messages';
        };

        $this->recordTestEvent($repository);

        $this->assertCount(1, DB::table('other_domain_messages')->get());
    }

    /** @test */
    public function it_can_register_a_consumer()
    {
        $consumer = new TestConsumer();

        $this->app->singleton(TestConsumer::class, function () use ($consumer) {
            return $consumer;
        });

        $repository = new class() extends Repository {
            protected $consumers = [
                TestConsumer::class,
            ];
        };

        $this->recordTestEvent($repository);

        $consumer->assertHandledMessageCount(1);
    }

    /** @test */
    public function using_a_queued_consumers_will_create_queued_job()
    {
        Queue::fake();

        $consumer = new TestConsumer();

        $this->app->singleton(TestConsumer::class, function () use ($consumer) {
            return $consumer;
        });

        $repository = new class() extends Repository {
            protected $queuedConsumers = [
                TestConsumer::class,
            ];
        };

        $this->recordTestEvent($repository);

        $consumer->assertHandledMessageCount(0);

        Queue::assertPushed(QueuedMessageJob::class, function (QueuedMessageJob $job) {
            if ($job->consumerClasses !== [TestConsumer::class]) {
                return false;
            }

            $firstMessage = $job->messages[0];
            if (! $firstMessage instanceof Message) {
                return false;
            }

            if ($firstMessage->aggregateRootId()->toString() !== '1') {
                return false;
            }

            if (! $firstMessage->event() instanceof TestEvent) {
                return false;
            }

            if ($firstMessage->event()->number !== 1) {
                return false;
            }

            return true;
        });
    }

    /** @test */
    public function the_queued_job_will_dispatch_the_message_to_the_consumers()
    {
        /**
         * We are going to use a queued consumer but not fake the queue.
         */
        $consumer = new TestConsumer();

        $this->app->singleton(TestConsumer::class, function () use ($consumer) {
            return $consumer;
        });

        $repository = new class() extends Repository {
            protected $queuedConsumers = [
                TestConsumer::class,
            ];
        };

        $this->recordTestEvent($repository);

        $consumer->assertHandledMessageCount(1);
    }

    /** @test */
    public function it_can_use_a_custom_queued_message_job_class()
    {
        Queue::fake();

        $consumer = new TestConsumer();

        $this->app->singleton(TestConsumer::class, function () use ($consumer) {
            return $consumer;
        });

        $repository = new class() extends Repository {
            protected $queuedConsumers = [
                TestConsumer::class,
            ];

            protected $queuedMessageJob = AlternativeQueuedMessageJob::class;
        };

        $this->recordTestEvent($repository);

        Queue::assertPushed(AlternativeQueuedMessageJob::class);
    }

    protected function recordTestEvent(Repository $repository)
    {
        $aggregateRoot = $repository->retrieve(new Identifier('1'));

        $aggregateRoot->testEvent(1);

        $repository->persist($aggregateRoot);
    }
}
