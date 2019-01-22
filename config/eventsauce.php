<?php

return [
    /*
     * We can generate  types, commands and events for you starting from a yaml file.
     * Here you can specify the input and the output.
     *
     * More info on code generation here:
     * https://eventsauce.io/docs/getting-started/create-events-and-commands
     */
    'code_generation' => [
        [
            'input_yaml_file' => null,
            'output_file' => null,
        ],
    ],

    /*
     * This class will be used by default to store events.
     *
     * You may change this to any class that implements
     * \EventSauce\EventSourcing\MessageRepository
     */
    'message_repository' => \Spatie\LaravelEventSauce\Models\StoredEvent::class,

    /*
     * This class will be used by default to put EventSauce message on the queued
     *
     * You may change this to any class that extends
     * \Spatie\LaravelEventSauce\QueuedMessageJob::class
     */
    'queued_message_job' => \Spatie\LaravelEventSauce\QueuedMessageJob::class,
];
