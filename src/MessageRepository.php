<?php

namespace Spatie\LaravelEventSauce;

use EventSauce\EventSourcing\AggregateRootId;
use EventSauce\EventSourcing\Header;
use EventSauce\EventSourcing\Message;
use EventSauce\EventSourcing\MessageRepository as EventSauceMessageRepository;
use EventSauce\EventSourcing\Serialization\MessageSerializer;
use Generator;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

class MessageRepository implements EventSauceMessageRepository
{
    /** @var \Illuminate\Database\Connection */
    protected $connection;

    /** @var string */
    protected $tableName;

    /** @var \EventSauce\EventSourcing\Serialization\MessageSerializer */
    protected $serializer;

    public function __construct(Connection $connection, string $tableName, MessageSerializer $serializer)
    {
        $this->connection = $connection;

        $this->tableName = $tableName;

        $this->serializer = $serializer;
    }

    public function persist(Message ...$messages)
    {
        foreach ($messages as $message) {
            $serializeMessage = $this->serializer->serializeMessage($message);

            $this->connection
                ->table($this->tableName)
                ->insert([
                    'event_id' => $serializeMessage['headers'][Header::EVENT_ID] ?? Uuid::uuid4()->toString(),
                    'event_type' => $serializeMessage['headers'][Header::EVENT_TYPE],
                    'aggregate_root_id' => $serializeMessage['headers'][Header::AGGREGATE_ROOT_ID] ?? null,
                    'payload' => json_encode($serializeMessage),
                    'recorded_at' => $serializeMessage['headers'][Header::TIME_OF_RECORDING],
                ]);
        }
    }

    public function retrieveAll(AggregateRootId $id): Generator
    {
        $payloads = $this->connection
            ->table($this->tableName)
            ->select('payload')
            ->where('aggregate_root_id', $id->toString())
            ->orderBy('recorded_at')
            ->get();

        foreach ($payloads as $payload) {
            yield from $this->serializer->unserializePayload(json_decode($payload->payload, true));
        }
    }
}
