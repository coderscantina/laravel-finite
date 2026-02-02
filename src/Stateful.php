<?php

namespace CodersCantina\LaravelFinite;

trait Stateful
{
    protected StateMachine $stateMachine;

    public function setStateMachine(StateMachine $stateMachine): self
    {
        $this->stateMachine = $stateMachine;

        return $this;
    }

    public function getState(): State
    {
        return $this->stateMachine->getCurrentState();
    }
}
