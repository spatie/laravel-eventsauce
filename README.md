**THIS PACKAGE IS STILL IN DEVELOPMENT**

# Use EventSauce in Laravel apps

[![Latest Version on Packagist](https://img.shields.io/packagist/v/spatie/laravel-eventsauce.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-eventsauce)
[![Build Status](https://img.shields.io/travis/spatie/laravel-eventsauce/master.svg?style=flat-square)](https://travis-ci.org/spatie/laravel-eventsauce)
[![Quality Score](https://img.shields.io/scrutinizer/g/spatie/laravel-eventsauce.svg?style=flat-square)](https://scrutinizer-ci.com/g/spatie/laravel-eventsauce)
[![StyleCI](https://github.styleci.io/repos/166579998/shield?branch=master)](https://github.styleci.io/repos/166579998)
[![Total Downloads](https://img.shields.io/packagist/dt/spatie/laravel-eventsauce.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-eventsauce)

[EventSauce](https://eventsauce.io/) is an easy way to introduce event sourcing into PHP projects.  This package allows EventSauce to make use of Laravel's migration, models and jobs. It can also help with generating code for commands and events. If you want to use EventSauce in a Laravel app, this package is the way to go!

Before using laravel-eventsauce you should already know how to work with EventSauce.

Here's a quick example of how to create a new aggregate root and matching repository. Let's run this command:

```php
php artisan make:aggregate-root  "MyDomain\MyAggregateRoot"
```

The `App\MyDomain\MyAggregateRoot` and `App\MyDomain\MyAggregateRootRepository` classes will have been created. This is how `MyAggregateRootRepository` looks like:

```php
namespace App\MyDomain;

use App\Domain\Account\Projectors\AccountProjector;
use App\Domain\Account\Projectors\TransactionCountProjector;
use Spatie\LaravelEventSauce\AggregateRootRepository;

/** @method \App\MyDomainMyAggregateRoot retrieve */
class MyAggregateRootRepository extends AggregateRootRepository
{
    /** @var string */
    protected $aggregateRoot = MyAggregateRoot::class;

    /** @var array */
    protected $consumers = [

    ];

    /** @var array */
    protected $queuedConsumers = [

    ];
}
```

You can put classnames of consumers in the `$consumers` array. Consumers in the `$queuedConsumers` array will called and be passed their messages using a queued job.

The `MyAggregateRootRepository` can be injected and used in any class.

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
                   $aggregateRoot->performCommand($command);
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
composer require eventsauce/laravel-eventsauce
```

This package comes with a migration to store all messages. You can publish the migration file using:

```bash
php artisan vendor:publish --provider="Spatie\LaravelEventSauce\EventSauceServiceProvider" --tag="migrations"
```

To create the `stored_messages` table, run the migrations

```bash
php artisan migrate
```

Next you must publish the `eventsauce` config file.

```php
php artisan vendor:publish --provider="EventSauce\LaravelEventSauce\EventSauceServiceProvider" --tag="config"
```

This is the contents of the file that will be publish to `config/eventsauce.php`

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
     * This class will be used by default to store events.
     *
     * You may change this to any class that implements
     * \EventSauce\EventSourcing\MessageRepository
     */
    'message_repository' => \Spatie\LaravelEventSauce\Models\StoredMessage::class,

    /*
     * This class will be used by default to put EventSauce message on the queued
     *
     * You may change this to any class that extends
     * \Spatie\LaravelEventSauce\QueuedMessageJob::class
     */
    'queued_message_job' => \Spatie\LaravelEventSauce\QueuedMessageJob::class,
];
```

## Usage

**COMING SOON**

### Code generation

We can generate types, events and commands from you starting from a yaml file. You can read more on the contents of the yaml file and the generated output in the "[Defining command and events using Yaml](https://eventsauce.io/docs/getting-started/create-events-and-commands/)" section of the EventSauce docs.

To generate code, fill in the keys in the `code_generation` parts of the `eventsauce` config file and execute this command

```
php artisan eventsauce:generate
```

### Testing

``` bash
./run-tests.sh
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
