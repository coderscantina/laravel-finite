<?php
namespace Neon\Finite;

use Illuminate\Support\Fluent;
use Neon\Finite\Accessor\FluentAccessor;
use PHPUnit\Framework\TestCase;

class StateMachineTest extends TestCase
{
    protected function getConfiguredStateMachine($config = null): \Neon\Finite\StateMachine
    {
        /** @noinspection PhpParamsInspection */
        $stateMachine = new StateMachine(new FluentAccessor);

        return $stateMachine->initialize(
            $config ?: [
                'states'      => [
                    'draft' => ['type' => 'initial']
                ],
                'transitions' => $this->getTransitions()
            ]
        );
    }

    protected function getTransitions(): array
    {
        return [
            'propose' => ['from' => ['draft'], 'to' => 'proposed', 'properties' => ['foo' => 'bar']],
            'accept'  => ['from' => ['proposed'], 'to' => 'accepted'],
            'refuse'  => ['from' => ['proposed'], 'to' => 'refused']
        ];
    }

    protected function getStates(): array
    {
        return [
            'draft'    => ['type' => 'initial'],
            'proposed' => [],
            'accepted' => ['type' => 'final'],
            'refused'  => ['type' => 'final'],
        ];
    }

    protected function getDefaultStateMachine(): StateMachine
    {
        /** @noinspection PhpParamsInspection */
        $stateMachine = new StateMachine(new FluentAccessor);
        $stateMachine->addState('draft', State::TYPE_INITIAL);
        $stateMachine->addState('proposed');
        $stateMachine->addState('accepted');
        $stateMachine->addState('refused');

        return $stateMachine;
    }

    /** @test */
    public function it_adds_a_single_state()
    {
        /** @noinspection PhpParamsInspection */
        $stateMachine = new StateMachine(new FluentAccessor);
        $stateMachine->addState('test');
        $this->assertCount(1, $stateMachine->getStates());
        $this->assertInstanceOf(State::class, $stateMachine->getStates()['test']);
    }

    /** @test */
    public function it_has_an_initial_state()
    {
        $stateMachine = $this->getDefaultStateMachine();

        $this->assertNotNull($stateMachine->getInitialState());
        $this->assertEquals('draft', $stateMachine->getInitialStateName());
    }

    /** @test */
    public function it_attaches_an_object_without_state()
    {
        $stateMachine = $this->getDefaultStateMachine();

        $stateMachine->setObject(new Fluent());
        $this->assertEquals('draft', $stateMachine->getCurrentStateName());
    }

    /** @test */
    public function it_attaches_an_object_with_state()
    {
        $stateMachine = $this->getDefaultStateMachine();

        $stateMachine->setObject(new Fluent(['state' => 'proposed']));
        $this->assertEquals('proposed', $stateMachine->getCurrentStateName());
    }

    /** @test */
    public function it_reads_a_state_config()
    {
        $stateMachine = $this->getConfiguredStateMachine(
            [
                'states' => $this->getStates()
            ]
        );
        $this->assertCount(4, $stateMachine->getStates());
        $this->assertEquals('draft', $stateMachine->getInitialStateName());
        $this->assertEquals('final', $stateMachine->getState('refused')->getType());
    }

    /** @test */
    public function it_reads_a_transition_config()
    {
        $stateMachine = $this->getConfiguredStateMachine(
            [
                'transitions' => $this->getTransitions()
            ]
        );

        $this->assertCount(3, $stateMachine->getTransitions());
        $this->assertEquals(['foo' => 'bar'], $stateMachine->getTransition('propose')->getProperties());
        $this->assertEquals('refuse', $stateMachine->getTransition('refuse')->getName());
        $this->assertEquals('refused', $stateMachine->getTransition('refuse')->getTo());
        $this->assertEquals(['proposed'], $stateMachine->getTransition('refuse')->getFrom()->toArray());
    }

    /** @test */
    public function it_reads_a_transition_config_and_appends_states()
    {
        $stateMachine = $this->getConfiguredStateMachine();

        $this->assertCount(4, $stateMachine->getStates());
        $this->assertContains('proposed', $stateMachine->getStates()->keys());
        $this->assertEquals(
            ['draft', 'proposed', 'accepted', 'refused'],
            $stateMachine->getStates()->keys()->toArray()
        );
    }

    /** @test */
    public function it_reads_adds_transitions_to_states()
    {
        $stateMachine = $this->getConfiguredStateMachine();

        $this->assertCount(1, $stateMachine->getState('draft')->getTransitions());
        $this->assertEquals('propose', $stateMachine->getState('draft')->getTransitions()[0]);
    }

    /** @test */
    public function it_can_check_transition_from_initial()
    {
        $stateMachine = $this->getConfiguredStateMachine();
        $stateMachine->setObject(new Fluent());

        $this->assertTrue($stateMachine->can('propose'));
        $this->assertFalse($stateMachine->can('accept'));
    }

    /** @test */
    public function it_fails_checking_on_an_unkown_transition()
    {
        $stateMachine = $this->getConfiguredStateMachine();
        $stateMachine->setObject(new Fluent());

        $this->assertFalse($stateMachine->can('foobar'));
    }

    /** @test */
    public function it_can_check_transition_from_normal_state()
    {
        $stateMachine = $this->getConfiguredStateMachine();
        $stateMachine->setObject(new Fluent(['state' => 'proposed']));

        $this->assertFalse($stateMachine->can('propose'));
        $this->assertTrue($stateMachine->can('accept'));
    }

    /** @test */
    public function it_applies_a_transition()
    {
        $stateMachine = $this->getConfiguredStateMachine();
        $stateMachine->setObject(new Fluent());

        $stateMachine->apply('propose');
        $this->assertEquals('proposed', $stateMachine->getCurrentStateName());
    }

    /** @test */
    public function it_fails_applying_an_invalid_transition()
    {
        $stateMachine = $this->getConfiguredStateMachine();
        $stateMachine->setObject(new Fluent());

        $stateMachine->apply('propose');
        $this->assertEquals('proposed', $stateMachine->getCurrentStateName());
    }

    /** @test */
    public function it_uses_a_setter()
    {
        $stateMachine = $this->getConfiguredStateMachine();
        $stateMachine->addTransition(
            'test',
            ['draft'],
            'test',
            null,
            function ($obj) {
                $obj['foo'] = 'bar';
            }
        );
        $o = new Fluent();
        $stateMachine->setObject($o);
        $stateMachine->apply('test');

        $this->assertEquals('bar', $o['foo']);
        $this->assertEquals('test', $stateMachine->getCurrentStateName());
    }

    /** @test */
    public function it_runs_a_guard()
    {
        $stateMachine = $this->getConfiguredStateMachine();
        $stateMachine->addTransition(
            'test',
            ['draft'],
            'test',
            null,
            null,
            [
                function ($obj) {
                    return $obj['allow_test'];
                }
            ]
        );
        $obj = new Fluent(
            [
                'allow_test' => false
            ]
        );
        $stateMachine->setObject($obj);

        $this->assertFalse($stateMachine->can('test'));
        $obj['allow_test'] = true;
        $this->assertTrue($stateMachine->can('test'));
    }

    /** @test */
    public function it_calls_a_listener()
    {
        $stateMachine = $this->getConfiguredStateMachine();
        $stateMachine->addTransition(
            'test',
            ['draft'],
            'test',
            null,
            null,
            null,
            [
                function (TransitionEvent $event) use (&$tmp) {
                    if ($event->isPre()) {
                        $tmp = $event;
                    }
                }
            ]
        );
        $stateMachine->setObject(new Fluent());
        $stateMachine->apply('test');

        $this->assertTrue($tmp->isPre());
    }

    /** @test */
    public function it_applies_properties_to_an_object()
    {
        $stateMachine = $this->getConfiguredStateMachine();
        $obj = new Fluent();
        $stateMachine->setObject($obj);

        $stateMachine->apply('propose');

        $this->assertEquals('bar', $obj->get('foo'));
    }

    /** @test */
    public function itHandlesClassBasedTransitions(): void
    {
        $stateMachine = $this->getConfiguredStateMachine(
            [
                'states'      => [
                    'draft' => ['type' => 'initial']
                ],
                'transitions' => [
                    'accept'  => new FooTransition('accept', ['draft'], 'accepted'),
                    'refuse'  => ['from' => ['draft'], 'to' => 'refused']
                ]
            ]);

        $obj = new Fluent();
        $stateMachine->setObject($obj);

        $stateMachine->apply('accept');

        $this->assertEquals('bar', $obj->get('foo'));
        $this->assertEquals('accepted', $obj->get('state'));
    }
}


class FooTransition extends Transition
{
    public function apply($obj)
    {
        $obj->state = $this->getTo();
        $obj->foo = 'bar';
    }

    public function can($obj): bool
    {
        return true;
    }
}
