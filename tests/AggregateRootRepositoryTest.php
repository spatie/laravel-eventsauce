<?php

namespace Spatie\LaravelEventSauce\Tests;

use Spatie\LaravelEventSauce\Tests\TestClasses\AggregateRoot;
use Spatie\LaravelEventSauce\Tests\TestClasses\AlternativeAggregateRoot;
use Spatie\LaravelEventSauce\Tests\TestClasses\Identifier;
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
}