<?php

namespace CodersCantina\LaravelFinite;

use Illuminate\Support\Collection;
use Closure;

class Transition
{
    protected string $name;
    protected Collection $from;
    protected ?string $to;
    protected Collection $guards;
    protected mixed $properties;
    protected ?Closure $setter;
    protected Collection $listeners;

    public function __construct(
        string $name,
        array $from = [],
        ?string $to = null,
        mixed $properties = null,
        ?Closure $setter = null,
        array $guards = [],
        array $listeners = []
    ) {
        $this->name = $name;
        $this->from = collect($from);
        $this->to = $to;
        $this->properties = $properties;
        $this->setter = $setter;
        $this->guards = collect($guards);
        $this->listeners = collect($listeners);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function addFrom(State|string $from): self
    {
        if ($from instanceof State) {
            $from = $from->getName();
        }

        $this->from[] = $from;

        return $this;
    }

    public function setFrom(array $values): self
    {
        foreach ($values as $from) {
            $this->addFrom($from);
        }

        return $this;
    }

    public function getFrom(): Collection
    {
        return $this->from;
    }

    public function getTo(): string
    {
        return $this->to;
    }

    public function setTo(State|string $to): self
    {
        if ($to instanceof State) {
            $to = $to->getName();
        }

        $this->to = $to;

        return $this;
    }

    public function getProperties(): mixed
    {
        return $this->properties;
    }

    public function hasProperties(): bool
    {
        return \is_array($this->properties) && \count($this->properties);
    }

    public function setProperties(mixed $properties): self
    {
        $this->properties = $properties;

        return $this;
    }

    public function getGuards(): Collection
    {
        return $this->guards;
    }

    public function setGuards(array $guards): self
    {
        $this->guards = collect($guards);

        return $this;
    }

    public function addGuard(Closure $guard): self
    {
        $this->guards[] = $guard;

        return $this;
    }

    public function hasGuards(): bool
    {
        return \count($this->guards) > 0;
    }

    public function setSetter(?Closure $setter): self
    {
        $this->setter = $setter;

        return $this;
    }

    public function getSetter(): ?Closure
    {
        return $this->setter;
    }

    public function hasSetter(): bool
    {
        return $this->setter !== null && \is_callable($this->setter);
    }

    public function getListeners(): Collection
    {
        return $this->listeners;
    }

    public function setListeners(array $listeners): self
    {
        $this->listeners = collect($listeners);

        return $this;
    }

    public function addListener(Closure $listener): self
    {
        $this->listeners[] = $listener;

        return $this;
    }

    public function hasListeners(): bool
    {
        return \count($this->listeners) > 0;
    }

    public function dispatchEvent(TransitionEvent $event): void
    {
        $this->listeners->each(fn(Closure $listener) => $listener($event));
    }

    public function hasApply(): bool
    {
        return is_callable([$this, 'apply']);
    }

    public function hasCan(): bool
    {
        return is_callable([$this, 'can']);
    }
}
