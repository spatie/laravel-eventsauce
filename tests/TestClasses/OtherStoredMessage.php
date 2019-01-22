<?php

namespace Spatie\LaravelEventSauce\Tests\TestClasses;

use Spatie\LaravelEventSauce\Models\StoredMessage;

class OtherStoredMessage extends StoredMessage
{
    public $table = 'other_stored_messages';
}
