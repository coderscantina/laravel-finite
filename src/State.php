<?php namespace Neon\Finite;

use Illuminate\Support\Collection;

class State
{
    const TYPE_INITIAL = 'initial';
    const TYPE_NORMAL = 'normal';
    const TYPE_FINAL = 'final';

    protected $type;
    protected $name;
    protected $properties;
    /**
     * @var Collection
     */
    protected $transitions;

    public function __construct($name, $type = self::TYPE_NORMAL, $transitions = [], $properties = [])
    {
        $this->name = $name;
        $this->type = $type;
        $this->transitions = collect($transitions);
        $this->properties = $properties;
    }

    public function isInitial()
    {
        return self::TYPE_INITIAL === $this->type;
    }

    public function isFinal()
    {
        return self::TYPE_FINAL === $this->type;
    }

    public function isNormal()
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

    public function addTransition($transition): self
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

    public function can($transition): bool
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
