<?php namespace Neon\Finite;

use Illuminate\Support\Collection;

class Transition
{
    protected $name;

    protected $from;

    protected $to;

    protected $guards;

    protected $properties;

    protected $listeners;

    public function __construct(
        string $name,
        $from = [],
        string $to = null,
        $properties = null,
        $guards = null,
        $listeners = null
    ) {
        $this->name = $name;
        $this->from = collect($from);
        $this->to = $to;
        $this->properties = $properties;
        $this->guards = collect($guards);
        $this->listeners = collect($listeners);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function addFrom($from): self
    {
        if ($from instanceof State) {
            $from = $from->getName();
        }

        $this->from[] = $from;
    }

    public function setFrom($values): self
    {
        foreach ($values as $from) {
            $this->addFrom($from);
        }

        return $this;
    }

    public function getFrom(): \Illuminate\Support\Collection
    {
        return $this->from;
    }

    public function getTo(): string
    {
        return $this->to;
    }

    public function setTo($to): self
    {
        if ($to instanceof State) {
            $to = $to->getName();
        }

        $this->to = $to;
    }

    /**
     * @return null|array|\ArrayAccess
     */
    public function getProperties()
    {
        return $this->properties;
    }

    public function hasProperties(): bool
    {
        return is_array($this->properties) && count($this->properties);
    }

    /**
     * @param null|array|\ArrayAccess $properties
     *
     * @return $this
     */
    public function setProperties($properties)
    {
        $this->properties = $properties;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getGuards()
    {
        return $this->guards;
    }

    /**
     * @param \Closure $guards
     *
     * @return $this
     */
    public function setGuards($guards)
    {
        $this->guards = collect($guards);

        return $this;
    }

    /**
     * @param \Closure $guard
     *
     * @return $this
     */
    public function addGuard($guard)
    {
        $this->guards[] = $guard;

        return $this;
    }

    public function hasGuards(): bool
    {
        return count($this->guards);
    }

    /**
     * @return Collection
     */
    public function getListeners()
    {
        return $this->listeners;
    }

    /**
     * @param \Closure $listeners
     *
     * @return $this
     */
    public function setListeners($listeners)
    {
        $this->listeners = collect($listeners);

        return $this;
    }

    /**
     * @param \Closure $listener
     *
     * @return $this
     */
    public function addListener($listener)
    {
        $this->listeners[] = $listener;

        return $this;
    }

    public function hasListeners(): bool
    {
        return count($this->listeners);
    }

    public function dispatchEvent($event)
    {
        $this->listeners->each(function($listener) use ($event) {
            $listener($event);
        });
    }
}
