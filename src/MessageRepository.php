<?php

namespace Spatie\LaravelEventSauce;

use EventSauce\EventSourcing\AggregateRootId;
use EventSauce\EventSourcing\Header;
use EventSauce\EventSourcing\Message;
use EventSauce\EventSourcing\MessageRepository as EventSauceMessageRepository;
use EventSauce\EventSourcing\Serialization\MessageSerializer;
use Generator;
use Illuminate\Database\Connection;
use Ramsey\Uuid\Uuid;

class MessageRepository implements EventSauceMessageRepository
{
    /** @var \Illuminate\Database\Connection */
    protected $connection;

    /** @var \EventSauce\EventSourcing\Serialization\MessageSerializer */
    protected $serializer;

    public function __construct(Connection $connection, MessageSerializer $serializer)
    {
        $this->serializer = $serializer;

        $this->connection = $connection;
    }

    public function persist(Message ...$messages)
    {
        foreach ($messages as $message) {
            $serialized = $this->serializer->serializeMessage($message);

            $eventId = $serialized['headers'][Header::EVENT_ID] ?? Uuid::uuid4()->toString();
            $type = $serialized['headers'][Header::EVENT_TYPE];
            $payload = json_encode($serialized);
            $recordedAt = $serialized['headers'][Header::TIME_OF_RECORDING];
            $aggregateRootId = $serialized['headers'][Header::AGGREGATE_ROOT_ID] ?? null;

            $this->connection->insert('
              INSERT INTO domain_messages
              (event_id, event_type, aggregate_root_id, recorded_at, payload)
              VALUES (?, ?, ?, ?, ?)
            ', [$eventId, $type, $aggregateRootId, $recordedAt, $payload]);
        }
    }

    public function retrieveAll(AggregateRootId $id): Generator
    {
        $payloads = $this->connection->select(
            'SELECT payload FROM domain_messages WHERE aggregate_root_id = ? ORDER BY recorded_at ASC',
            [$id->toString()]
        );

        foreach ($payloads as $payload) {
            yield from $this->serializer->unserializePayload(json_decode($payload->payload, true));
        }
    }
}
