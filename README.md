# Finite State Machine for Laravel/Eloquent models

[![Latest Version on Packagist](https://img.shields.io/packagist/v/coderscantina/laravel-finite.svg?style=flat-square)](https://packagist.org/packages/coderscantina/laravel-finite)
[![License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![PHP Version](https://img.shields.io/packagist/php-v/coderscantina/laravel-finite.svg?style=flat-square)](https://packagist.org/packages/coderscantina/laravel-finite)

A powerful finite state machine (FSM) library for Laravel/Eloquent models. Manage complex state workflows with a simple, fluent API.

## Features

- 🎯 **Simple & Intuitive API** - Define states and transitions with ease
- 🔄 **State Management** - Apply transitions to Eloquent models automatically
- 📋 **Transition Rules** - Define allowed transitions between states
- 🎁 **Properties** - Apply properties during transitions
- 🎧 **Event Listeners** - Listen to pre/post transition events
- 🛡️ **Guards** - Control transitions with guard closures
- 📦 **Fluent & Trait Accessors** - Work with both Eloquent models and Fluent objects
- ✅ **Laravel 10, 11, 12 Support** - Compatible with modern Laravel versions

## Requirements

- PHP 8.1 or higher
- Laravel 10, 11, or 12

## Installation

Install the package via Composer:

```bash
composer require coderscantina/laravel-finite
```

For Laravel 5.5+, the service provider is automatically discovered. For older versions, add the service provider to your `config/app.php`:

```php
CodersCantina\LaravelFinite\ServiceProvider::class,
```

## Quick Start

### 1. Add StateTrait to Your Model

```php
<?php

use Illuminate\Database\Eloquent\Model;
use CodersCantina\LaravelFinite\StateMachine;
use CodersCantina\LaravelFinite\StateTrait;

class Article extends Model
{
    use StateTrait;

    protected static function initializeState(StateMachine $stateMachine): StateMachine
    {
        $stateMachine->initialize([
            'states' => [
                'draft'     => ['type' => 'initial'],
                'submitted' => ['type' => 'normal'],
                'published' => ['type' => 'final'],
                'rejected'  => ['type' => 'final'],
            ],
            'transitions' => [
                'submit'  => ['from' => ['draft'], 'to' => 'submitted'],
                'publish' => ['from' => ['submitted'], 'to' => 'published'],
                'reject'  => ['from' => ['submitted'], 'to' => 'rejected'],
                'draft'   => ['from' => ['submitted', 'rejected'], 'to' => 'draft'],
            ]
        ]);

        return $stateMachine;
    }
}
```

### 2. Store the State

Add a `state` column to your model's migration:

```php
Schema::create('articles', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->text('content');
    $table->string('state')->default('draft');
    $table->timestamps();
});
```

### 3. Use State Transitions

```php
$article = Article::create(['title' => 'My Article']);

// Check current state
echo $article->getState(); // 'draft'

// Check if transition is possible
if ($article->canTransition('submit')) {
    $article->applyTransition('submit');
}

// Or apply directly (throws InvalidStateException if not allowed)
$article->applyTransition('submit');
echo $article->getState(); // 'submitted'
```

## State Types

Define different state types to control workflow:

- **initial** - Starting state for new models
- **normal** - Intermediate states in the workflow
- **final** - Terminal states (no further transitions allowed)

```php
'states' => [
    'draft'     => ['type' => 'initial'],
    'submitted' => ['type' => 'normal'],
    'published' => ['type' => 'final'],
]
```

## Transitions

### Basic Transitions

```php
'transitions' => [
    'publish' => [
        'from' => ['draft', 'submitted'],  // Can transition from these states
        'to'   => 'published',              // To this state
    ]
]
```

### Transitions with Properties

Automatically set properties when transitioning:

```php
'transitions' => [
    'publish' => [
        'from'       => ['submitted'],
        'to'         => 'published',
        'properties' => [
            'published_at' => now(),
            'is_active'    => true,
        ]
    ]
]
```

### Transitions with Setters

Use a closure to customize state changes:

```php
'transitions' => [
    'publish' => [
        'from'   => ['submitted'],
        'to'     => 'published',
        'setter' => function ($model) {
            $model->published_at = now();
            $model->published_by = auth()->id();
        }
    ]
]
```

### Transitions with Guards

Control transitions with guard closures:

```php
'transitions' => [
    'publish' => [
        'from'   => ['submitted'],
        'to'     => 'published',
        'guards' => [
            function ($model) {
                return $model->user->is_admin; // Only admins can publish
            }
        ]
    ]
]
```

### Transitions with Listeners

Listen to pre/post transition events:

```php
'transitions' => [
    'publish' => [
        'from'      => ['submitted'],
        'to'        => 'published',
        'listeners' => [
            function ($event) {
                if ($event->isPre()) {
                    Log::info('Publishing article: ' . $event->getObject()->id);
                } elseif ($event->isPost()) {
                    Mail::send(new ArticlePublished($event->getObject()));
                }
            }
        ]
    ]
]
```

## Advanced Usage

### Manual State Machine Usage

```php
use CodersCantina\LaravelFinite\StateMachine;
use CodersCantina\LaravelFinite\Accessor\FluentAccessor;
use Illuminate\Support\Fluent;

$stateMachine = new StateMachine(new FluentAccessor);
$stateMachine->initialize([
    'states' => [
        'draft'    => ['type' => 'initial'],
        'proposed' => [],
        'accepted' => ['type' => 'final'],
        'refused'  => ['type' => 'final'],
    ],
    'transitions' => [
        'propose' => ['from' => ['draft'], 'to' => 'proposed'],
        'accept'  => ['from' => ['proposed'], 'to' => 'accepted'],
        'refuse'  => ['from' => ['proposed'], 'to' => 'refused'],
    ]
]);

$item = new Fluent();
$stateMachine->setObject($item);

// Use the state machine
$stateMachine->apply('propose');
echo $stateMachine->getCurrentStateName(); // 'proposed'
```

### Adding States Dynamically

```php
$stateMachine->addState('archived', 'final');
$stateMachine->addTransition('archive', ['published'], 'archived');
```

### Checking Transitions

```php
// Check if a transition is possible
$stateMachine->can('publish'); // bool

// Get all transitions
$stateMachine->getTransitions(); // Collection

// Get current state
$stateMachine->getCurrentState(); // State object

// Get all states
$stateMachine->getStates(); // Collection
```

### Class-Based Transitions

Create custom transition classes for complex logic:

```php
use CodersCantina\LaravelFinite\Transition;

class PublishTransition extends Transition
{
    public function apply($model)
    {
        $model->state = $this->getTo();
        $model->published_at = now();
        $model->published_by = auth()->id();
        $model->save();
    }

    public function can($model): bool
    {
        return $model->user->is_admin;
    }
}

// Use in configuration
'transitions' => [
    'publish' => new PublishTransition('publish', ['submitted'], 'published'),
]
```

## API Reference

### Model Methods (StateTrait)

- `getState()` - Get the current state value
- `setState($state)` - Set the state value
- `getStateMachine()` - Get the StateMachine instance
- `canTransition($transition)` - Check if a transition is possible
- `applyTransition($transition, $payload = null)` - Apply a transition
- `applyProperties($properties)` - Apply properties to the model

### StateMachine Methods

- `initialize($config)` - Initialize with configuration array
- `setObject($obj)` - Attach an object to the state machine
- `can($transition)` - Check if transition is possible
- `apply($transition, $payload = null)` - Apply a transition
- `addState($name, $type, $properties)` - Add a state
- `addTransition($name, $from, $to, $properties, $setter, $guards, $listeners)` - Add a transition
- `getState($name)` - Get a specific state
- `getCurrentState()` - Get the current state
- `getCurrentStateName()` - Get the current state name
- `getStates()` - Get all states
- `getTransitions()` - Get all transitions
- `getTransition($name)` - Get a specific transition

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Testing

Run the test suite:

```bash
composer test
```

## Security

If you discover any security-related issues, please email m.wallner@coderscantina.com instead of using the issue tracker.

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## About

Created and maintained by [Coders Cantina](https://coderscantina.com).
