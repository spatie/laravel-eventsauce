<?php

namespace Spatie\LaravelEventSauce;

use EventSauce\EventSourcing\MessageRepository;
use Illuminate\Support\ServiceProvider;
use Spatie\LaravelEventSauce\Commands\GenerateCodeCommand;
use EventSauce\EventSourcing\Serialization\MessageSerializer;
use EventSauce\EventSourcing\Serialization\ConstructingMessageSerializer;
use Spatie\LaravelEventSauce\Commands\MakeAggregateRootCommand;

class EventSauceServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/eventsauce.php' => config_path('eventsauce.php'),
            ], 'config');

            $this->publishes([
                __DIR__.'/../database/migrations' => database_path('migrations'),
            ], 'migrations');

            if (! class_exists('CreateStatusesTable')) {
                $timestamp = date('Y_m_d_His', time());

                $this->publishes([
                    __DIR__.'/../database/migrations/create_stored_events_table.php.stub' => database_path('migrations/'.$timestamp.'_create_stored_events_table.php'),
                ], 'migrations');
            }
        }
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/eventsauce.php', 'eventsauce');

        $this->commands([
            GenerateCodeCommand::class,
            MakeAggregateRootCommand::class,
        ]);

        $this->app->bind(MessageSerializer::class, function () {
            return new ConstructingMessageSerializer();
        });

        $this->app->bind(MessageRepository::class, function () {
            $messageRepositoryClass = config('eventsauce.message_repository');

            return app($messageRepositoryClass);
        });

        return $this;
    }

    public function provides()
    {
        return [
            GenerateCodeCommand::class,
            MakeAggregateRootCommand::class,
        ];
    }
}
