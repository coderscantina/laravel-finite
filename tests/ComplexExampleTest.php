<?php namespace Neon\Finite;

use Illuminate\Support\Fluent;
use Neon\Finite\Accessor\FluentAccessor;
use PHPUnit\Framework\TestCase;

class ComplexExampleTest extends TestCase
{
    protected $config;

    protected function setUp()
    {
        parent::setUp();

        $this->config = [
            'states'      => [
                'draft'     => ['type' => 'initial', 'properties' => []],
                'review'    => ['type' => 'normal', 'properties' => []],
                'proposed'  => ['type' => 'normal', 'properties' => []],
                'confirmed' => ['type' => 'normal', 'properties' => []],
                'accepted'  => ['type' => 'final', 'properties' => []],
                'refused'   => ['type' => 'final', 'properties' => []],
            ],
            'transitions' => [
                'propose' => ['from' => ['draft', 'review'], 'to' => 'proposed'],
                'review'  => ['from' => ['draft', 'proposed'], 'to' => 'review'],
                'redraft' => ['from' => ['proposed', 'review'], 'to' => 'draft'],
                'confirm' => ['from' => ['review', 'proposed'], 'to' => 'confirmed'],
                'accept'  => ['from' => ['proposed'], 'to' => 'accepted'],
                'refuse'  => ['from' => ['proposed', 'confirmed'], 'to' => 'refused'],
            ]
        ];
    }

    /** @test */
    public function it_runs_a_complex_state_example()
    {
        /** @noinspection PhpParamsInspection */
        $stateMachine = new StateMachine(new FluentAccessor);
        $stateMachine->initialize($this->config);
        $stateMachine->setObject(new Fluent());

        $this->assertEquals('draft', $stateMachine->getCurrentStateName());
        $this->assertTrue($stateMachine->can('review'));
        $this->assertTrue($stateMachine->can('propose'));
        $this->assertFalse($stateMachine->can('accept'));

        $stateMachine->apply('review');
        $this->assertEquals('review', $stateMachine->getCurrentStateName());
        $this->assertTrue($stateMachine->can('redraft'));
        $this->assertTrue($stateMachine->can('propose'));
        $this->assertFalse($stateMachine->can('accept'));

        $stateMachine->apply('redraft');
        $stateMachine->apply('review');
        $stateMachine->apply('propose');
        $this->assertEquals('proposed', $stateMachine->getCurrentStateName());
        $this->assertTrue($stateMachine->can('redraft'));
        $this->assertTrue($stateMachine->can('confirm'));
        $this->assertTrue($stateMachine->can('accept'));
        $this->assertFalse($stateMachine->can('propose'));

        $stateMachine->apply('confirm');
        $this->assertEquals('confirmed', $stateMachine->getCurrentStateName());
    }
}
