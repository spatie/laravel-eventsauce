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

class StoredEvent extends Model implements MessageRepository
{
    public $timestamps = false;

    public function persist(Message ...$messages)
    {
        foreach ($messages as $message) {
            $serialized = $this->getMessageSerializer()->serializeMessage($message);

            $storedEvent = new static();

            $storedEvent->event_id = $serialized['headers'][Header::EVENT_ID] ?? Uuid::uuid4()->toString();
            $storedEvent->event_type = $serialized['headers'][Header::EVENT_TYPE];
            $storedEvent->aggregate_root_id = $serialized['headers'][Header::AGGREGATE_ROOT_ID] ?? null;
            $storedEvent->payload = json_encode($serialized);
            $storedEvent->recorded_at = $serialized['headers'][Header::TIME_OF_RECORDING];

            $storedEvent->save();
        }
    }

    public function retrieveAll(AggregateRootId $id): Generator
    {
        $payloads = static::query()
            ->select('payload')
            ->where('aggregate_root_id', $id->toString())
            ->orderBy('recorded_at')
            ->get();

        foreach ($payloads as $payload) {
            yield from $this->getMessageSerializer()->unserializePayload(json_decode($payload->payload, true));
        }
    }

    protected function getMessageSerializer(): MessageSerializer
    {
        return app(MessageSerializer::class);
    }
}
