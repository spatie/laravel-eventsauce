<?php

namespace Spatie\LaravelEventSauce;

use EventSauce\EventSourcing\ConstructingAggregateRootRepository;
use EventSauce\EventSourcing\MessageDispatcherChain;
use EventSauce\EventSourcing\Serialization\ConstructingMessageSerializer;
use EventSauce\EventSourcing\Serialization\MessageSerializer;
use EventSauce\EventSourcing\SynchronousMessageDispatcher;
use Illuminate\Support\ServiceProvider;
use Spatie\LaravelEventSauce\Commands\GenerateCodeCommand;

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

        $this
            ->registerAggregateRoots()
            ->registerMessageDispatcherChain()
            ->registerMessageSerializer()
            ->registerSynchronousDispatcher()
            ->registerAsyncDispatcher()
            ->bindAsyncDispatcherToJob();
    }

    public function provides()
    {
        return [
            GenerateCodeCommand::class,
        ];
    }

    protected function registerAggregateRoots()
    {
        foreach (config('eventsauce.aggregate_roots') as $aggregateRootConfig) {
            $this->app->bind(
                $aggregateRootConfig['repository'],
                function () use ($aggregateRootConfig) {
                    return new ConstructingAggregateRootRepository(
                        $aggregateRootConfig['aggregate_root'],
                        config('eventsauce.aggregate_roots'),
                        app(MessageDispatcherChain::class)
                    );
                }
            );
        }

        return $this;
    }

    protected function registerMessageDispatcherChain()
    {
        $this->app->bind(MessageDispatcherChain::class, function () {
            $dispatcher = config('eventsauce.dispatcher');

            return new MessageDispatcherChain(
                app($dispatcher),
                app(SynchronousMessageDispatcher::class)
            );
        });

        return $this;
    }

    protected function registerMessageSerializer()
    {
        $this->app->bind(MessageSerializer::class, function () {
            return new ConstructingMessageSerializer();
        });

        return $this;
    }

    protected function registerSynchronousDispatcher()
    {
        $this->app->bind(SynchronousMessageDispatcher::class, function () {
            $consumers = array_map(function ($consumerName) {
                return app($consumerName);
            }, $this->getConfigForAllAggregateRoots('sync_consumers'));

            return new SynchronousMessageDispatcher(...$consumers);
        });

        return $this;
    }

    protected function registerAsyncDispatcher()
    {
        $this->app->bind('eventsauce.async_dispatcher', function () {
            $consumers = array_map(function ($consumerName) {
                return app($consumerName);
            }, $this->getConfigForAllAggregateRoots('async_consumers'));

            return new SynchronousMessageDispatcher(...$consumers);
        });

        return $this;
    }

    protected function bindAsyncDispatcherToJob()
    {
        $this->app->bindMethod(EventSauceJob::class.'@handle', function (EventSauceJob $job) {
            $dispatcher = app('eventsauce.async_dispatcher');

            $job->handle($dispatcher);
        });

        return $this;
    }

    protected function getConfigForAllAggregateRoots(string $key): array
    {
        $result = data_get(config('eventsauce'), "aggregate_roots.*.{$key}");

        return array_flatten($result);
    }
}
