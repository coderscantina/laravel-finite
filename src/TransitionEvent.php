<?php

namespace CodersCantina\LaravelFinite;

class TransitionEvent
{
    public const string PRE = 'pre';
    public const string POST = 'post';
    public const string TEST = 'test';

    protected Transition $transition;
    protected string $type;
    protected object $obj;

    public function __construct(Transition $transition, object $obj, string $type)
    {
        $this->type = $type;
        $this->transition = $transition;
        $this->obj = $obj;
    }

    public function isPre(): bool
    {
        return $this->type === self::PRE;
    }

    public function isPost(): bool
    {
        return $this->type === self::POST;
    }

    public function isTest(): bool
    {
        return $this->type === self::TEST;
    }

    public function getObject(): object
    {
        return $this->obj;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getTransition(): Transition
    {
        return $this->transition;
    }
}
