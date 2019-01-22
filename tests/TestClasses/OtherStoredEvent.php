<?php

namespace Spatie\LaravelEventSauce\Tests\TestClasses;

use Spatie\LaravelEventSauce\Models\StoredEvent;

class OtherStoredEvent extends StoredEvent
{
    public $table = 'other_stored_events';
}
