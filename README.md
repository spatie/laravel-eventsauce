# Use EventSauce in Laravel apps

[![Latest Version on Packagist](https://img.shields.io/packagist/v/spatie/laravel-eventsauce.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-eventsauce)
[![Build Status](https://img.shields.io/travis/spatie/laravel-eventsauce/master.svg?style=flat-square)](https://travis-ci.org/spatie/laravel-eventsauce)
[![Quality Score](https://img.shields.io/scrutinizer/g/spatie/laravel-eventsauce.svg?style=flat-square)](https://scrutinizer-ci.com/g/spatie/laravel-eventsauce)
[![Total Downloads](https://img.shields.io/packagist/dt/spatie/laravel-eventsauce.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-eventsauce)

[EventSauce](https://eventsauce.io/) is an easy way to introduce event sourcing into PHP projects.  This package allows EventSauce to make use of Laravel's migrations, models and jobs. It can also help with generating code for commands and events. If you want to use EventSauce in a Laravel app, this package is the way to go!

Before using laravel-eventsauce you should already know how to work with EventSauce.

Here's a quick example of how to create a new aggregate root and matching repository. Let's run this command:

```php
php artisan make:aggregate-root "MyDomain\MyAggregateRoot"
```

The `App\MyDomain\MyAggregateRoot` and `App\MyDomain\MyAggregateRootRepository` classes will have been created. A migration to create `my_aggregate_root_domain_messages` will have been added to your application too.  This is how `MyAggregateRootRepository` looks like:

```php
namespace App\MyDomain;

use App\Domain\Account\Projectors\AccountProjector;
use App\Domain\Account\Projectors\TransactionCountProjector;
use Spatie\LaravelEventSauce\AggregateRootRepository;

/** @method \App\MyDomain\MyAggregateRoot retrieve */
class MyAggregateRootRepository extends AggregateRootRepository
{
    /** @var string */
    protected $aggregateRoot = MyAggregateRoot::class;

    /** @var string */
    protected $tableName = 'my_aggregate_root_domain_messages';

    /** @var array */
    protected $consumers = [

    ];

    /** @var array */
    protected $queuedConsumers = [

    ];
}
```

You can put classnames of consumers in the `$consumers` array. Consumers in the `$queuedConsumers` array will called and be passed their messages using a queued job.

The `MyAggregateRootRepository` can be injected and used in any class. In this example we assume you've manually created a `performMySpecialCommand` method on `MyAggregateRoot`:

```php
namespace App\MyDomain;

class CommandHandler
{
    /** @var \EventSauce\EventSourcing\AggregateRootRepository */
    private $repository;

    public function __construct(MyAggregateRootRepository $repository)
    {
        $this->repository = $repository;
    }

    public function handle(object $command)
    {
        $aggregateRootId = $command->identifier();

        $aggregateRoot = $this->repository->retrieve($aggregateRootId);

        try {
            if ($command instanceof MySpecialCommand) {
               $aggregateRoot->performMySpecialCommand($command);
            } 
        } finally {
            $this->repository->persist($aggregateRoot);
        }
    }
}
```

## Installation

You can install the package via composer:

```bash
composer require spatie/laravel-eventsauce
```

Next you must publish the `eventsauce` config file.

```bash
php artisan vendor:publish --provider="Spatie\LaravelEventSauce\EventSauceServiceProvider" --tag="config"
```

This is the contents of the file that will be publish to `config/eventsauce.php`:

```php
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
     * This connection name will be used to store messages. When
     * set to null the default connection will be used.
     */
    'database_connection' => null,

    /*
     * This class will be used to store messages.
     *
     * You may change this to any class that implements
     * \EventSauce\EventSourcing\MessageRepository
     */
    'message_repository' => \Spatie\LaravelEventSauce\MessageRepository::class,

    /*
     * This class will be used to put EventSauce messages on the queue.
     *
     * You may change this to any class that extends
     * \Spatie\LaravelEventSauce\QueuedMessageJob::class
     */
    'queued_message_job' => \Spatie\LaravelEventSauce\QueuedMessageJob::class,
];
```

## Usage

### Generating an aggregate root and repository

An aggregate root and matching repository can be generated used this command

```bash
php artisan make:aggregate-root "MyDomain\MyAggregateRoot"
```

This command will create `App\MyDomain\MyAggregateRoot` and `App\MyDomain\MyAggregateRootRepository`. 

This is how `MyAggregateRootRepository` looks like:

```php
namespace App\MyDomain;

use App\Domain\Account\Projectors\AccountProjector;
use App\Domain\Account\Projectors\TransactionCountProjector;
use Spatie\LaravelEventSauce\AggregateRootRepository;

/** @method \App\MyDomain\MyAggregateRoot retrieve */
class MyAggregateRootRepository extends AggregateRootRepository
{
    /** @var string */
    protected $aggregateRoot = MyAggregateRoot::class;
    
    /** @var string */
    protected $tableName = 'my_aggregate_root_domain_messages';

    /** @var array */
    protected $consumers = [

    ];

    /** @var array */
    protected $queuedConsumers = [

    ];
}
```

If you repository doesn't need consumers or queued consumers, you can safely remove those variables. The only required variable is `$aggregateRoot`.

Of course you can also manually create an aggregate root repository. Just create a class, let it extend`Spatie\LaravelEventSauce\AggregateRootRepository`. Next, put the fully qualified classname of your aggregate root in a protected `$aggregateRoot` property. Finally add a `$tableName` property containing the name of the table where you want to store domain messages.

### Configuring the aggregate root repository

#### Specifying the aggregate root

The `$aggregateRoot` property should contain the fully qualied class name of an aggregate root. A valid aggregate root is any class that implements `EventSauce\EventSourcing\AggregateRoot`

#### Adding consumers

Consumers are classes that receive all events and do something with them, for example creation a projection. The `$consumers` property should be an array that contains class names of consumers. A valid consumer is any class that implements `EventSauce\EventSourcing\Consumer`.

#### Adding queued consumers

Unless you need the result of a consumer in the same request as your command or event is fired, it's recommanded to let a consumer to perform it's work on a queue. The `$queuedConsumers` property should be an array that contains class names of consumers. A valid consumer is any class that implements `EventSauce\EventSourcing\Consumer`.

If there are any message that needs to be sent to any of these consumers, the package will dispatch a `Spatie\LaravelEventSauce\QueuedMessageJob` by default.

#### Customizing the job that passes messages to queued consumers

By default `Spatie\LaravelEventSauce\QueuedMessageJob` is used to pass messages to queued consumers. You can customized this job by setting the `queued_message_job` entry in the `eventsauce` config file to the class of your custom job. A valid job is any class that extends `Spatie\LaravelEventSauce\QueuedMessageJob`. 

Changing the `queued_message_job` entry will change the default job of all aggregate root repositories. If you want to change the job class for a specific repository add a `$queuedMessageJob` property to that repository.

Here is an example:

```php
// ...

class MyAggregateRootRepository extends AggregateRootRepository
{
    // ...
    
    protected $queuedMessageJob = MyCustomJob::class;
}
```

You can use that custom job to add properties to control the timeout, max attempts and the queue to be used. You can read more on how to configure a job in the [Laravel docs on queueing](https://laravel.com/docs/master/queues).

Here's an example of a custom job.

```php
use Spatie\LaravelEventSauce\QueuedMessageJob;

class MyCustomJob extends QueuedMessageJob
{
    /*
     * The name of the connection the job should be sent to.
     */
    public $connection = 'my-custom-connection';

    /*
     * The name of the queue the job should be sent to.
     */
    public $queue = 'my-custom-queue';

    /*
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 5;
    
    /*
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 120;
    
    /*
     * The number of seconds before the job should be made available.
     *
     * @var int|null
     */
    public $delay;
}
```

### Customizing the table name where messages are stored

The `$tableName` property on your aggregate root repository determines where messages are being stored. You can change this to any name you want as long as you've created a a table with that name that has the following columns:

```php
Schema::create('custom_table_name', function (Blueprint $table) {
    $table->increments('id');
    $table->string('event_id', 36);
    $table->string('event_type', 100);
    $table->string('aggregate_root_id', 36)->nullable()->index();
    $table->dateTime('recorded_at', 6)->index();
    $table->text('payload');
});
```

### Specifying a connection

Laravel has support for multiple database connections. By default the aggregate root will use Laravel's default connection. If you want all your aggregate roots to use a the same alternative connection then specify that connection name in the `connection` property of the `eventsauce` config file.

If you want let a specific repository use an alternative connection, you can just specify it's name in the `$connection` property 

```php
// ...

class MyAggregateRootRepository extends AggregateRootRepository
{
    // ...
    
    protected $connection = 'connection-name';
}
```

### Code generation

We can generate types, events and commands from you starting from a yaml file. You can read more on the contents of the yaml file and the generated output in the "[Defining command and events using Yaml](https://eventsauce.io/docs/getting-started/create-events-and-commands/)" section of the EventSauce docs.

To generate code, fill in the keys in the `code_generation` parts of the `eventsauce` config file and execute this command.

```
php artisan eventsauce:generate
```

### Testing

``` bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email freek@spatie.be instead of using the issue tracker.

## Postcardware

You're free to use this package, but if it makes it to your production environment we highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using.

Our address is: Spatie, Samberstraat 69D, 2060 Antwerp, Belgium.

We publish all received postcards [on our company website](https://spatie.be/en/opensource/postcards).


## Credits

- [Freek Van der Herten](https://github.com/freekmurze)

The initial version of this package was based upon a development version of [LaravelEventSauce](https://github.com/EventSaucePHP/LaravelEventSauce).

## Support us

Spatie is a webdesign agency based in Antwerp, Belgium. You'll find an overview of all our open source projects [on our website](https://spatie.be/opensource).

Does your business depend on our contributions? Reach out and support us on [Patreon](https://www.patreon.com/spatie). 
All pledges will be dedicated to allocating workforce on maintenance and new awesome stuff.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
