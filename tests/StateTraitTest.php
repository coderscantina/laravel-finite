<?php namespace Neon\Finite;

use Illuminate\Database\Eloquent\Model;

class StateTraitTest extends AbstractTestCase
{
    /** @test */
    public function it_inits_the_statemachine()
    {
        $model = new TestModel();

        $this->assertEquals('draft', $model->getState());
        $this->assertTrue($model->canTransition('propose'));
        $this->assertFalse($model->canTransition('accept'));
    }

    /** @test */
    public function it_allows_an_own_constructor()
    {
        $model = new TestModel2();

        $this->assertTrue($model->set_in_constructor);
        $this->assertEquals('draft', $model->getState());
        $this->assertTrue($model->canTransition('propose'));
        $this->assertFalse($model->canTransition('accept'));
    }

    /** @test */
    public function it_applies_transitions_on_a_model()
    {
        $model = new TestModel();

        $model->applyTransition('propose');
        $model->applyTransition('accept');

        $this->assertEquals('accepted', $model->getState());
    }
}

class TestModel extends Model
{
    use StateTrait;

    protected static function initializeState(StateMachine $stateMachine): StateMachine
    {
        $stateMachine->initialize(
            [
                'states'      => [
                    'draft' => ['type' => 'initial']
                ],
                'transitions' => [
                    'propose' => ['from' => ['draft'], 'to' => 'proposed'],
                    'accept'  => ['from' => ['proposed'], 'to' => 'accepted'],
                    'refuse'  => ['from' => ['proposed'], 'to' => 'refused']
                ]
            ]
        );

        return $stateMachine;
    }
}

class TestModel2 extends Model
{
    use StateTrait;

    public function __construct()
    {
        parent::__construct();

        $this->initializeStateTrait();
        $this->set_in_constructor = true;
    }

    protected static function initializeState(StateMachine $stateMachine): StateMachine
    {
        $stateMachine->initialize(
            [
                'states'      => [
                    'draft' => ['type' => 'initial']
                ],
                'transitions' => [
                    'propose' => ['from' => ['draft'], 'to' => 'proposed'],
                    'accept'  => ['from' => ['proposed'], 'to' => 'accepted'],
                    'refuse'  => ['from' => ['proposed'], 'to' => 'refused']
                ]
            ]
        );

        return $stateMachine;
    }
}
