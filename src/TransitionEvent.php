<?php namespace Neon\Finite;

class TransitionEvent
{
    const PRE = 'pre';

    const POST = 'post';

    const TEST = 'test';

    /**
     * @var Transition
     */
    protected $transition;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var
     */
    protected $obj;

    /**
     * TransitionEvent constructor.
     *
     * @param Transition $transition
     * @param $obj
     * @param string $type
     */
    function __construct(Transition $transition, $obj, $type)
    {
        $this->type = $type;
        $this->transition = $transition;
        $this->obj = $obj;
    }

    public function isPre()
    {
        return $this->type === self::PRE;
    }

    public function isPost()
    {
        return $this->type === self::POST;
    }

    public function isTest()
    {
        return $this->type === self::TEST;
    }

    public function getObject()
    {
        return $this->obj;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getTransition()
    {
        return $this->transition;
    }

}