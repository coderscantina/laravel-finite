<?php

namespace CodersCantina\LaravelFinite;

use CodersCantina\LaravelFinite\Accessor\FluentAccessor;
use CodersCantina\LaravelFinite\Accessor\TraitAccessor;

class ServiceProviderTest extends AbstractTestCase
{
    /** @test */
    public function it_makes_a_statemachine_instance()
    {
        /** @var StateMachine $stateMachine */
        $stateMachine = $this->app->make('StateMachine');

        $this->assertTrue($stateMachine instanceof StateMachine);
        $this->assertTrue($stateMachine->getAccessor() instanceof TraitAccessor);
    }

    /** @test */
    public function it_makes_a_fluent_statemachine_instance()
    {
        /** @var StateMachine $stateMachine */
        $stateMachine = $this->app->make('FluentStateMachine');

        $this->assertTrue($stateMachine instanceof StateMachine);
        $this->assertTrue($stateMachine->getAccessor() instanceof FluentAccessor);
    }
}
