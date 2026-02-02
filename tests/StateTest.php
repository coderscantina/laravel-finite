<?php

namespace CodersCantina\LaravelFinite;

class StateTest extends \PHPUnit\Framework\TestCase
{
    /** @test */
    public function it_has_name()
    {
        $state = new State('test');

        $this->assertEquals('test', $state->getName());
    }

    /** @test */
    public function it_defaults_to_normal_type()
    {
        $state = new State('test');

        $this->assertEquals(State::TYPE_NORMAL, $state->getType());
    }

    /** @test */
    public function it_adds_a_transition()
    {
        $state = new State('');
        $state->addTransition('test');

        $transitions = $state->getTransitions();
        $this->assertCount(1, $transitions);
        $this->assertEquals('test', $transitions[0]);
    }

    /** @test */
    public function it_returns_collection_of_transition_names()
    {
        $state = new State('');
        $state->addTransition('t1');
        $state->addTransition('t2');

        $this->assertEquals(collect(['t1', 't2']), $state->getTransitions());
    }
}
