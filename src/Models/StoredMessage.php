<?php

namespace Spatie\LaravelEventSauce\Models;

use Generator;
use Ramsey\Uuid\Uuid;
use EventSauce\EventSourcing\Header;
use EventSauce\EventSourcing\Message;
use Illuminate\Database\Eloquent\Model;
use EventSauce\EventSourcing\AggregateRootId;
use EventSauce\EventSourcing\MessageRepository;
use EventSauce\EventSourcing\Serialization\MessageSerializer;

class StoredMessage extends Model implements MessageRepository
{
    public $timestamps = false;

    public $guarded = [];

    public $casts = [
        'payload' => 'array',
    ];

    public function persist(Message ...$messages)
    {
        foreach ($messages as $message) {
            $serializedMessage = $this->getMessageSerializer()->serializeMessage($message);

            $headers = $serializedMessage['headers'];

            static::create([
                'event_id' => $headers[Header::EVENT_ID] ?? Uuid::uuid4()->toString(),
                'event_type' => $headers[Header::EVENT_TYPE],
                'aggregate_root_id' => $headers[Header::AGGREGATE_ROOT_ID] ?? null,
                'payload' => $serializedMessage,
                'recorded_at' => $headers[Header::TIME_OF_RECORDING],
            ]);
        }
    }

    public function retrieveAll(AggregateRootId $id): Generator
    {


        $storedEvents = static::query()
            ->select('payload')
            ->where('aggregate_root_id', $id->toString())
            ->orderBy('recorded_at')
            ->get();
        foreach ($storedEvents as $storedEvent) {
            yield from $this->getMessageSerializer()->unserializePayload($storedEvent->payload);
        }
    }

    protected function getMessageSerializer(): MessageSerializer
    {
        return app(MessageSerializer::class);
    }
}
