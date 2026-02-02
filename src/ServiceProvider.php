<?php

namespace CodersCantina\LaravelFinite;

use CodersCantina\LaravelFinite\Accessor\FluentAccessor;
use CodersCantina\LaravelFinite\Accessor\TraitAccessor;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function register()
    {
        $this->app->bind(
            'StateMachine',
            fn() => new StateMachine(new TraitAccessor())
        );

        $this->app->bind(
            'FluentStateMachine',
            fn() => new StateMachine(new FluentAccessor())
        );
    }
}
