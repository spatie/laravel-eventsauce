<?php

return [
    'aggregate_roots' => [
        [
            /*
             * An aggregate root is an entity that is modelled using events.
             *
             * See: https://eventsauce.io/docs/getting-started/create-an-aggregate-root/
             */
            'aggregate_root' => null,

            /*
             * Consumers are classes that listen to events and do something with them, for
             * example projecting data.
             */
            'sync_consumers' => [
                // ...
            ],

            /*
             * These consumers will be executing in a queued job.
             */
            'async_consumers' => [
                // ...
            ],

            /*
             * We can generate  types, commands and events for you starting from a yaml file.
             * Here you can specify the input and the output.
             *
             * More info on code generation here: https://eventsauce.io/docs/getting-started/create-events-and-commands
             */
            'code_generation' => [
                'input_yaml_file' => null,
                'output_file' => null,
            ],
        ],
    ],

    /*
     * This class is responsible for dispatching jobs.
     *
     * It should implement \EventSauce\EventSourcing\MessageDispatcher
     */
    'dispatcher' => \EventSauce\LaravelEventSauce\MessageDispatcher::class,

    /*
     * This class is responsible for storing and retrieving events.
     *
     * It should implement \EventSauce\EventSourcing\MessageRepository
     */
    'repository' => \EventSauce\LaravelEventSauce\MessageRepository::class,
];
