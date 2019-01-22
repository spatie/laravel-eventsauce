<?php

namespace Spatie\LaravelEventSauce\Tests;

use Illuminate\Support\Facades\DB;
use Spatie\LaravelEventSauce\Models\StoredEvent;
use Spatie\LaravelEventSauce\Tests\TestClasses\AggregateRoot;
use Spatie\LaravelEventSauce\Tests\TestClasses\AlternativeAggregateRoot;
use Spatie\LaravelEventSauce\Tests\TestClasses\Identifier;
use Spatie\LaravelEventSauce\Tests\TestClasses\OtherStoredEvent;
use Spatie\LaravelEventSauce\Tests\TestClasses\Repository;

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

        $aggregateRoot = $repository->retrieve(new Identifier('1'));

        $aggregateRoot->testEvent(1);

        $repository->persist($aggregateRoot);

        $this->assertCount(1, StoredEvent::get());
    }

    /** @test */
    public function it_can_use_an_alternative_message_repository()
    {
        DB::select('CREATE TABLE `other_stored_events` AS SELECT * FROM `stored_events` WHERE 0
');

        $repository = new class() extends Repository {
            protected $messageRepository = OtherStoredEvent::class;
        };

        $aggregateRoot = $repository->retrieve(new Identifier('1'));

        $aggregateRoot->testEvent(1);

        $repository->persist($aggregateRoot);

        $this->assertCount(1, DB::table('other_stored_events')->get());
    }
}