<?php

namespace Spatie\LaravelEventSauce;

use Illuminate\Support\ServiceProvider;
use Spatie\LaravelEventSauce\Commands\GenerateCodeCommand;
use EventSauce\EventSourcing\Serialization\MessageSerializer;
use EventSauce\EventSourcing\Serialization\ConstructingMessageSerializer;

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
        }
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/eventsauce.php', 'eventsauce');

        $this->commands([
            GenerateCodeCommand::class,
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
        ];
    }
}
