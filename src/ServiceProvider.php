<?php namespace Neon\Finite;

use Neon\Finite\Accessor\FluentAccessor;
use Neon\Finite\Accessor\TraitAccessor;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function boot()
    {
    }

    public function register()
    {
        $this->app->bind('StateMachine', function () {
            return new StateMachine(new TraitAccessor());
        });

        $this->app->bind('FluentStateMachine', function () {
            return new StateMachine(new FluentAccessor());
        });
    }
}
