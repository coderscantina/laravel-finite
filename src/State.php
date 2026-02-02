<?php

namespace CodersCantina\LaravelFinite;

use Illuminate\Support\Collection;

class State
{
    public const string TYPE_INITIAL = 'initial';
    public const string TYPE_NORMAL = 'normal';
    public const string TYPE_FINAL = 'final';

    protected string $type;
    protected string $name;
    protected array $properties;
    protected Collection $transitions;

    public function __construct(string $name, string $type = self::TYPE_NORMAL, array $transitions = [], array $properties = [])
    {
        $this->name = $name;
        $this->type = $type;
        $this->transitions = collect($transitions);
        $this->properties = $properties;
    }

    public function isInitial(): bool
    {
        return self::TYPE_INITIAL === $this->type;
    }

    public function isFinal(): bool
    {
        return self::TYPE_FINAL === $this->type;
    }

    public function isNormal(): bool
    {
        return self::TYPE_NORMAL === $this->type;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getTransitions(): Collection
    {
        return $this->transitions;
    }

    public function addTransition(string|Transition $transition): self
    {
        if ($transition instanceof Transition) {
            $transition = $transition->getName();
        }
        $this->transitions[] = $transition;

        return $this;
    }

    public function addTransitions(array $transitions): self
    {
        foreach ($transitions as $transition) {
            $this->addTransition($transition);
        }

        return $this;
    }

    public function can(string|Transition $transition): bool
    {
        if ($transition instanceof Transition) {
            $transition = $transition->getName();
        }

        return $this->transitions->contains($transition);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getProperties(): array
    {
        return $this->properties;
    }

    public function setProperties(array $properties): self
    {
        $this->properties = $properties;

        return $this;
    }
}
