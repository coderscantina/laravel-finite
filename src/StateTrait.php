<?php namespace Neon\Finite;

trait StateTrait
{
    /** @var StateMachine */
    protected $stateMachine;

    public function __construct()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        parent::__construct();

        $this->initStateMachineTrait();
    }

    public abstract static function initializeState(StateMachine $stateMachine): StateMachine;

    protected function getStateMachine()
    {
        return $this->stateMachine;
    }

    public function applyProperties($properties)
    {
        foreach ($properties as $name => $value) {
            $object[$name] = $value;
        }

        return $this;
    }

    public function applyTransition($transition)
    {
        $this->stateMachine->apply($transition);

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

    protected function initStateMachineTrait()
    {
        $this->stateMachine = self::initializeState(app('StateMachine'));
        $this->stateMachine->setObject($this);
    }
}
