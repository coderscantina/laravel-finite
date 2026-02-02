<?php

namespace CodersCantina\LaravelFinite;

trait StateTrait
{
    protected StateMachine $stateMachine;

    public static function bootStateTrait(): void
    {
        static::created(fn($item) => $item->initializeStateTrait());
        static::retrieved(fn($item) => $item->initializeStateTrait());
    }

    abstract protected static function initializeState(StateMachine $stateMachine): StateMachine;

    protected function getStateMachine(): StateMachine
    {
        return $this->stateMachine;
    }

    public function applyProperties(array $properties): self
    {
        foreach ($properties as $name => $value) {
            $this->$name = $value;
        }

        return $this;
    }

    public function applyTransition(string $transition, mixed $payload = null): self
    {
        $this->stateMachine->apply($transition, $payload);

        return $this;
    }

    public function canTransition(string $transition): bool
    {
        return $this->stateMachine->can($transition);
    }

    public function getState(): ?string
    {
        return $this['state'] ?? null;
    }

    public function setState(string $state): self
    {
        $this['state'] = $state;

        return $this;
    }

    protected function initializeStateTrait(): void
    {
        $this->stateMachine = $this->initializeState(app('StateMachine'));
        $this->stateMachine->setObject($this);
    }
}
