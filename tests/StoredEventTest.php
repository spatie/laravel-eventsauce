<?php

namespace Spatie\LaravelEventSauce\Tests;

use DateTimeImmutable;
use EventSauce\EventSourcing\Header;
use EventSauce\EventSourcing\Message;
use EventSauce\EventSourcing\PointInTime;
use Spatie\LaravelEventSauce\Models\StoredEvent;
use Spatie\LaravelEventSauce\Tests\TestClasses\TestEvent;

class StoredEventTest extends TestCase
{
    /** @test */
    public function it_can_store_a_message()
    {
        $testEvent = new TestEvent(1);

        $headers = [
            Header::EVENT_ID => 1,
            Header::EVENT_TYPE => 'TestEvent',
            Header::AGGREGATE_ROOT_ID => 'aggregate-root-id',
            Header::TIME_OF_RECORDING => PointInTime::fromDateTime(new DateTimeImmutable())->toString(),
        ];

        $message = new Message($testEvent, $headers);

        (new StoredEvent())->persist($message);

        $storedEvent = StoredEvent::first();

        $this->assertEquals($headers[Header::EVENT_ID], $storedEvent->event_id);
        $this->assertEquals($headers[Header::EVENT_TYPE], $storedEvent->event_type);
        $this->assertEquals($headers[Header::AGGREGATE_ROOT_ID], $storedEvent->aggregate_root_id);
        $this->assertEquals($headers[Header::TIME_OF_RECORDING], $storedEvent->recorded_at);

        $this->assertCount(4, $storedEvent->payload['headers']);
        $this->assertEquals(1, $storedEvent->payload['payload']['amount']);
    }
}