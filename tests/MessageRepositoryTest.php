<?php

namespace Spatie\LaravelEventSauce\Tests;

use DateTimeImmutable;
use EventSauce\EventSourcing\Header;
use EventSauce\EventSourcing\Message;
use EventSauce\EventSourcing\MessageRepository;
use EventSauce\EventSourcing\PointInTime;
use Illuminate\Support\Facades\DB;
use Spatie\LaravelEventSauce\Models\StoredMessage;
use Spatie\LaravelEventSauce\Tests\TestClasses\TestEvent;
use Spatie\LaravelEventSauce\Tests\TestClasses\Identifier;

class MessageRepositoryTest extends TestCase
{
    /** @var \EventSauce\EventSourcing\MessageRepository */
    protected $messageRepository;

    public function setUp()
    {
        parent::setUp();

        $messageRepositoryClass = config('eventsauce.message_repository');

        $this->messageRepository = app()->makeWith($messageRepositoryClass, ['tableName' => 'domain_messages']);
    }

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

        $this->messageRepository->persist($message);

        $storedDomainMessage = DB::table('domain_messages')->first();

        $this->assertEquals($headers[Header::EVENT_ID], $storedDomainMessage->event_id);
        $this->assertEquals($headers[Header::EVENT_TYPE], get_class($testEvent));
        $this->assertEquals($headers[Header::AGGREGATE_ROOT_ID], $storedDomainMessage->aggregate_root_id);
        $this->assertEquals($headers[Header::TIME_OF_RECORDING], $storedDomainMessage->recorded_at);

        $payload = json_decode($storedDomainMessage->payload, true);

        $this->assertCount(4, $payload['headers']);
        $this->assertEquals(1, $payload['payload']['amount']);
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

        $this->messageRepository->persist($message);

        $identifier = new Identifier(1);

        $messages = $this->messageRepository->retrieveAll($identifier);

        $messageArray = [];

        foreach ($messages as $message) {
            $messageArray[] = $message;
        }

        $this->assertCount(1, $messageArray);
        $this->assertInstanceOf(Message::class, $messageArray[0]);
        $this->assertInstanceOf(TestEvent::class, $messageArray[0]->event());
    }
}
