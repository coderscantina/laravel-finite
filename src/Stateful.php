<?php namespace Neon\Finite;

trait Stateful
{
    /** @var StateMachine */
    protected $stateMachine;

    public function setStateMachine(StateMachine $stateMachine)
    {
        $this->stateMachine = $stateMachine;

        return $this;
    }

    public function getState()
    {
        return $this->stateMachine->getCurrentState();
    }
}
