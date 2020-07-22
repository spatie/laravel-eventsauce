<?php

namespace Spatie\LaravelEventSauce;

use EventSauce\EventSourcing\Serialization\ConstructingMessageSerializer;
use EventSauce\EventSourcing\Serialization\MessageSerializer;
use Illuminate\Support\ServiceProvider;
use Spatie\LaravelEventSauce\Commands\GenerateCodeCommand;
use Spatie\LaravelEventSauce\Commands\MakeAggregateRootCommand;

class EventSauceServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/eventsauce.php' => config_path('eventsauce.php'),
            ], 'config');
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
