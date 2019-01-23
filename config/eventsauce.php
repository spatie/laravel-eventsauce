<?php

return [
    /*
     * Types, commands and events can be generated starting from a yaml file.
     * Here you can specify the input and the output of the code generation.
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
     * This class will be used to store events.
     *
     * You may change this to any class that implements
     * \EventSauce\EventSourcing\MessageRepository
     */
    'message_repository' => \Spatie\LaravelEventSauce\Models\StoredMessage::class,

    /*
     * This class will be used to put EventSauce messages on the queue.
     *
     * You may change this to any class that extends
     * \Spatie\LaravelEventSauce\QueuedMessageJob::class
     */
    'queued_message_job' => \Spatie\LaravelEventSauce\QueuedMessageJob::class,
];
