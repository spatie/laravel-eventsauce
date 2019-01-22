<?php

namespace Spatie\LaravelEventSauce\Tests;

use DateTimeImmutable;
use EventSauce\EventSourcing\Header;
use EventSauce\EventSourcing\Message;
use EventSauce\EventSourcing\PointInTime;
use Spatie\LaravelEventSauce\Models\StoredMessage;
use Spatie\LaravelEventSauce\Tests\TestClasses\Identifier;
use Spatie\LaravelEventSauce\Tests\TestClasses\TestEvent;

class StoredMessageTest extends TestCase
{
    /** @test */
    public function it_can_store_a_message()
    {
        $testEvent = new TestEvent(1);

        $headers = [
            Header::EVENT_ID => 1,
            Header::EVENT_TYPE => get_class($testEvent),
            Header::AGGREGATE_ROOT_ID => 'aggregate-root-id',
            Header::TIME_OF_RECORDING => PointInTime::fromDateTime(new DateTimeImmutable())->toString(),
        ];

        $message = new Message($testEvent, $headers);

        (new StoredMessage())->persist($message);

        $storedEvent = StoredMessage::first();

        $this->assertEquals($headers[Header::EVENT_ID], $storedEvent->event_id);
        $this->assertEquals($headers[Header::EVENT_TYPE], get_class($testEvent));
        $this->assertEquals($headers[Header::AGGREGATE_ROOT_ID], $storedEvent->aggregate_root_id);
        $this->assertEquals($headers[Header::TIME_OF_RECORDING], $storedEvent->recorded_at);

        $this->assertCount(4, $storedEvent->payload['headers']);
        $this->assertEquals(1, $storedEvent->payload['payload']['amount']);
    }

    /** @test */
    public function it_can_retrieve_messages()
    {
        $testEvent = new TestEvent(1);

        $headers = [
            Header::EVENT_ID => 1,
            Header::EVENT_TYPE => get_class($testEvent),
            Header::AGGREGATE_ROOT_ID => '1',
            Header::TIME_OF_RECORDING => PointInTime::fromDateTime(new DateTimeImmutable())->toString(),
        ];

        $message = new Message($testEvent, $headers);

        (new StoredMessage())->persist($message);

        $identifier = new Identifier(1);

        $messages = (new StoredMessage())->retrieveAll($identifier);

        $messageArray = [];

        foreach($messages as $message) {
            $messageArray[] = $message;
        }

        $this->assertCount(1, $messageArray);
        $this->assertInstanceOf(Message::class, $messageArray[0]);
        $this->assertInstanceOf(TestEvent::class, $messageArray[0]->event());
    }
}