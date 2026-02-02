<?php

namespace CodersCantina\LaravelFinite;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use CodersCantina\LaravelFinite\Accessor\Accessor;
use Closure;
use ArrayAccess;

class StateMachine
{
    protected object $obj;
    protected Accessor $accessor;
    protected Collection $transitions;
    protected Collection $states;
    protected State $currentState;

    public function __construct(Accessor $accessor)
    {
        $this->accessor = $accessor;
        $this->transitions = collect();
        $this->states = collect();
    }

    public function initialize(array $config = []): self
    {
        foreach (Arr::get($config, 'states', []) as $name => $state) {
            $this->addState($name, Arr::get($state, 'type') ?? State::TYPE_NORMAL, Arr::get($state, 'properties') ?? []);
        }

        foreach (Arr::get($config, 'transitions', []) as $name => $transition) {
            if ($transition instanceof Transition) {
                $this->addTransition($transition);
            } else {
                $this->addTransition(
                    $name,
                    Arr::get($transition, 'from'),
                    Arr::get($transition, 'to'),
                    Arr::get($transition, 'properties'),
                    Arr::get($transition, 'setter'),
                    Arr::get($transition, 'guards'),
                    Arr::get($transition, 'listeners')
                );
            }
        }

        return $this;
    }

    public function setObject(object $obj): self
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

    public function addTransition(
        Transition|string $transition,
        array|null $initialStates = null,
        string|null $finalState = null,
        array|ArrayAccess|null $properties = null,
        Closure|null $setter = null,
        array|null $guards = null,
        array|null $listeners = null
    ): self {
        if (!$transition instanceof Transition) {
            $transition = new Transition(
                $transition,
                $initialStates ?? [],
                $finalState,
                $properties,
                $setter,
                $guards ?? [],
                $listeners ?? []
            );
        }

        $this->transitions[$transition->getName()] = $transition;

        collect($transition->getFrom())->each(
            function ($item) {
                if (!$this->states->offsetExists($item)) {
                    $this->addState($item);
                }
            }
        );

        if (!$this->states->offsetExists($transition->getTo())) {
            $this->addState($transition->getTo());
        }

        $transition->getFrom()->each(
            function (string $state) use ($transition) {
                $this->getState($state)->addTransition($transition);
            }
        );

        return $this;
    }

    public function getTransitions(): Collection
    {
        return $this->transitions;
    }

    public function getTransition(string $name): ?Transition
    {
        return $this->transitions->get($name);
    }

    public function can(Transition|string $transition): bool
    {
        $transition = $transition instanceof Transition ? $transition : $this->getTransition($transition);

        if ($transition === null) {
            return false;
        }

        if ($transition->hasGuards() && !$this->accessor->callGuards($this->obj, $transition->getGuards())) {
            return false;
        }

        if ($transition->hasCan() && !$transition->can($this->obj)) {
            return false;
        }

        return $this->getCurrentState()->getTransitions()->contains($transition->getName());
    }

    public function apply(Transition|string $transition, mixed $payload = null): void
    {
        $t = $transition instanceof Transition ? $transition : $this->getTransition($transition);
        if ($t === null) {
            throw new InvalidStateException("Unkown Transition `{$transition}`.");
        }

        if (!$this->can($t)) {
            throw new InvalidStateException(
                "Transition `{$t->getName()}` is not possible in current state `{$this->getCurrentStateName()}`"
            );
        }

        if ($t->hasApply()) {
            $t->apply($this->obj, $payload);

            return;
        }

        $this->dispatchEvent($t, $this->obj, TransitionEvent::PRE);

        $this->accessor->setState($this->obj, $t->getTo());
        if ($t->hasProperties()) {
            $this->accessor->applyProperties($this->obj, $t->getProperties());
        }

        if ($payload) {
            $this->accessor->applyProperties($this->obj, $payload);
        }

        if ($t->hasSetter()) {
            $setter = $t->getSetter();
            $setter($this->obj);
        }

        $this->currentState = $this->getState($t->getTo());
        $this->dispatchEvent($t, $this->obj, TransitionEvent::POST);
    }

    public function addState(
        State|string $state,
        string $type = State::TYPE_NORMAL,
        array $properties = []
    ): self {
        if (!$state instanceof State) {
            $state = new State($state, $type, [], $properties);
        }

        $this->states[$state->getName()] = $state;

        return $this;
    }

    public function getInitialState(): ?State
    {
        return $this->states->first(fn($state) => $state->isInitial());
    }

    public function getInitialStateName(): string
    {
        $initial = $this->getInitialState();
        if ($initial) {
            return $initial->getName();
        }

        throw new InvalidStateException('No initial state found');
    }

    public function getStates(): Collection
    {
        return $this->states;
    }

    public function getState(string $state): ?State
    {
        return $this->states->get($state);
    }

    public function getCurrentState(): State
    {
        return $this->currentState;
    }

    public function getCurrentStateName(): string
    {
        return $this->currentState->getName();
    }

    public function getAccessor(): Accessor
    {
        return $this->accessor;
    }

    protected function dispatchEvent(Transition $transition, object $obj, string $type): void
    {
        $event = new TransitionEvent($transition, $obj, $type);
        $transition->dispatchEvent($event);
    }
}
