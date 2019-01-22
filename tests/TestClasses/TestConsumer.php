<?php

namespace Spatie\LaravelEventSauce\Tests\TestClasses;

use EventSauce\EventSourcing\Consumer;
use EventSauce\EventSourcing\Message;
use PHPUnit\Framework\Assert;

class TestConsumer implements Consumer
{
    protected $handledMessages = [];

    public function handle(Message $message)
    {
        $this->handledMessages[] = $message;
    }

    public function assertHandledMessageCount(int $expectedCount)
    {
        $actualCount = count($this->handledMessages);

        Assert::assertCount($expectedCount, $this->handledMessages, "Expected {$expectedCount} handled messages, but {$actualCount} were actually handled.");
    }
}