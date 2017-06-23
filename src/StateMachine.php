<?php namespace Neon\Finite;

use Illuminate\Support\Arr;
use Neon\Finite\Accessor\Accessor;

class StateMachine
{
    protected $obj;

    /** @var Accessor */
    protected $accessor;

    /** @var \Illuminate\Support\Collection */
    protected $transitions;

    /** @var \Illuminate\Support\Collection */
    protected $states;

    /** @var State */
    protected $currentState;

    public function __construct(Accessor $accessor)
    {
        $this->accessor = $accessor;

        $this->transitions = collect();
        $this->states = collect();
    }

    public function initialize($config = []): self
    {
        foreach (Arr::get($config, 'states', []) as $name => $cfg) {
            $this->addState($name, Arr::get($cfg, 'type'), Arr::get($cfg, 'properties'));
        }

        foreach (Arr::get($config, 'transitions', []) as $name => $cfg) {
            $this->addTransition(
                $name,
                Arr::get($cfg, 'from'),
                Arr::get($cfg, 'to'),
                Arr::get($cfg, 'properties'),
                Arr::get($cfg, 'guards'),
                Arr::get($cfg, 'listeners')
            );
        }

        return $this;
    }

    public function setObject($obj): self
    {
        $this->obj = $obj;
        $initialState = $this->accessor->getState($obj);
        if ($initialState === null) {
            $initialState = $this->getInitialStateName();
            $this->accessor->setState($obj, $initialState);
        }
        $this->currentState = $this->getState($initialState);

        return $this;
    }

    /**
     * @param string|Transition $transition
     * @param null|State[] $initialStates
     * @param null|State $finalState
     * @param null|array|\ArrayAccess $properties
     * @param null|\Closure[] $guards
     * @param null|\Closure[] $listeners
     *
     * @return StateMachine
     */
    public function addTransition(
        $transition,
        $initialStates = null,
        $finalState = null,
        $properties = null,
        $guards = null,
        $listeners = null
    ): self {
        if (!$transition instanceof Transition) {
            $transition = new Transition($transition, $initialStates, $finalState, $properties, $guards, $listeners);
        }

        $this->transitions[$transition->getName()] = $transition;

        collect($initialStates)->each(function ($item) {
            if (!$this->states->offsetExists($item)) {
                $this->addState($item);
            }
        });

        if (!$this->states->offsetExists($finalState)) {
            $this->addState($finalState);
        }

        $transition->getFrom()->each(function (string $state) use ($transition) {
            $this->getState($state)->addTransition($transition);
        });

        return $this;
    }

    /**
     * @return \Illuminate\Support\Collection|Transition[]
     */
    public function getTransitions(): \Illuminate\Support\Collection
    {
        return $this->transitions;
    }

    /**
     * @param $name
     *
     * @return Transition|null
     */
    public function getTransition($name)
    {
        return $this->transitions->get($name);
    }

    /**
     * @param string|Transition $transition
     *
     * @return bool
     */
    public function can($transition): bool
    {
        $transition = $transition instanceof Transition ? $transition : $this->getTransition($transition);

        if ($transition === null || ($transition->hasGuards() && !$this->accessor->callGuards($this->obj,
                    $transition->getGuards()))
        ) {
            return false;
        }

        return $this->getCurrentState()->getTransitions()->contains($transition->getName());
    }

    public function apply($transition)
    {
        $t = $transition instanceof Transition ? $transition : $this->getTransition($transition);
        if ($t === null) {
            throw new InvalidStateException("Unkown Transition `{$transition}`.");
        }

        if (!$this->can($t)) {
            throw new InvalidStateException("Transition `{$t->getName()}` is not possible in current state `{$this->getCurrentStateName()}`");
        }

        $this->dispatchEvent($t, $this->obj, TransitionEvent::PRE);

        $this->accessor->setState($this->obj, $t->getTo());
        if ($t->hasProperties()) {
            $this->accessor->applyProperties($this->obj, $t->getProperties());
        }
        $this->currentState = $this->getState($t->getTo());
        $this->dispatchEvent($t, $this->obj, TransitionEvent::POST);
    }

    /**
     * @param string|State $state
     * @param string $type
     * @param array $properties
     *
     * @return StateMachine
     */
    public function addState($state, $type = State::TYPE_NORMAL, $properties = []): self
    {
        if (!$state instanceof State) {
            $state = new State($state, $type, $properties);
        }

        $this->states[$state->getName()] = $state;

        return $this;
    }

    /**
     * @return State|Null
     */
    public function getInitialState(): State
    {
        return $this->states->first->isInitial();
    }

    /**
     * @return string
     * @throws InvalidStateException
     */
    public function getInitialStateName()
    {
        $initial = $this->getInitialState();
        if ($initial) {
            return $initial->getName();
        }

        throw new InvalidStateException('No initial state found');
    }

    /**
     * @return \Illuminate\Support\Collection|State[]
     */
    public function getStates(): \Illuminate\Support\Collection
    {
        return $this->states;
    }

    /**
     * @param $state
     *
     * @return State|null
     */
    public function getState($state)
    {
        return $this->states->get($state);
    }

    /**
     * @return State
     */
    public function getCurrentState(): State
    {
        return $this->currentState;
    }

    /**
     * @return string
     */
    public function getCurrentStateName()
    {
        return $this->currentState->getName();
    }

    /**
     * @return Accessor
     */
    public function getAccessor(): Accessor
    {
        return $this->accessor;
    }

    /**
     * @param Transition $transition
     * @param $obj
     * @param $type
     */
    protected function dispatchEvent($transition, $obj, $type)
    {
        $event = new TransitionEvent($transition, $obj, $type);
        $transition->dispatchEvent($event);
    }
}
