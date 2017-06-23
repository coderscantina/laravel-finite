# Neon Finite

Finite state machine for Laravel/Eloquent models.

## Features

* Manage state within Eloquent models
* Define allowed transitions
* Apply properties while transition
* Provides events to listen on

## Getting started

* Install this package
* Use the `\Neon\Finite\StateTrait` in your model
* Define your desired states in your app using a `ServicesProvider::boot` method

## Install

Require this package with composer:

``` bash
$ composer require neon/finite
```

After updating composer, add the ServiceProvider to the providers array in config/app.php

> Laravel 5.5 uses Package Auto-Discovery, so doesn't require you to manually add the ServiceProvider

### Laravel 5.0 – 5.4:

```php
Neon\Finite\ServiceProvider::class,
```

## Usage

### In an Eloquent model

```php
<?php

use \Illuminate\Database\Eloquent\Model;
use \Neon\Finite\StateMachine;
use \Neon\Finite\StateTrait;

class TestModel extends Model {
    use StateTrait {
        __construct as traitConstructor;
    }

    public function __construct() {
        $this->traitConstructor();
        $this->set_in_constructor = true;
    }

    static function initializeState(StateMachine $stateMachine): StateMachine
    {
        $stateMachine->initialize([
            'states'      => [
                'draft' => ['type' => 'initial']
            ],
            'transitions' => [
                'propose' => ['from' => ['draft'], 'to' => 'proposed'],
                'accept'  => ['from' => ['proposed'], 'to' => 'accepted'],
                'refuse'  => ['from' => ['proposed'], 'to' => 'refused']
            ]
        ]);

        return $stateMachine;
    }
}

```

### Apply a transition

```php
<?php

$model = new TestModel;
$model->apply('propose');
```

### Test for a transition

```php
<?php

$model = new TestModel;
$model->can('propose');

```


### Manual Usage

#### Initialize the State Machine  

``` php
$stateMachine = new Neon\Finite\StateMachine();
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
```

#### Add a State
``` php
$stateMachine->addState('draft', 'initial');
```

#### Add a Transition
``` php
$stateMachine->addTransition('propose', ['draft'], 'proposed');
```

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Testing

``` bash
$ composer test
```

## Security

If you discover any security related issues, please email m.wallner@neonblack.at instead of using the issue tracker.

