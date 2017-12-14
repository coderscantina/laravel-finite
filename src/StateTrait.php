<?php namespace Neon\Finite;

trait StateTrait
{
    /** @var StateMachine */
    protected $stateMachine;

    public static function bootStateTrait()
    {
        static::created(function($item) {
            $item->initStateMachineTrait($item);
        });
        static::retrieved(function($item) {
            $item->initStateMachineTrait($item);
        });
    }

    public abstract static function initializeState(StateMachine $stateMachine): StateMachine;

    protected function getStateMachine()
    {
        return $this->stateMachine;
    }

    public function applyProperties($properties)
    {
        foreach ($properties as $name => $value) {
            $this->$name = $value;
        }

        return $this;
    }

    public function applyTransition($transition, $payload = null)
    {
        $this->stateMachine->apply($transition, $payload);

        return $this;
    }

    public function canTransition($transition)
    {
        return $this->stateMachine->can($transition);
    }

    public function getState()
    {
        return $this['state'];
    }

    public function setState($state): self
    {
        $this['state'] = $state;

        return $this;
    }

    protected function initStateMachineTrait($item)
    {
        $item->stateMachine = $item->initializeState(app('StateMachine'));
        $item->stateMachine->setObject($item);
    }
}
