<?php

namespace Spatie\LaravelEventSauce\Tests\TestClasses;

use Spatie\LaravelEventSauce\QueuedMessageJob;

class AlternativeQueuedMessageJob extends QueuedMessageJob
{
    public $queue = 'alternative-queue';
}
