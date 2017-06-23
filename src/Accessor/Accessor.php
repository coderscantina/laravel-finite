<?php namespace Neon\Finite\Accessor;

use Neon\Finite\StateTrait;

interface Accessor
{
    /**
     * @param $object
     * @param string $state
     *
     * @return self
     */
    public function setState($object, $state);

    /**
     * @param StateTrait $object
     *
     * @return string|null
     */
    public function getState($object);

    /**
     * @param $object
     * @param array $properties
     *
     * @return self
     */
    public function applyProperties($object, $properties);

    /**
     * @param $object
     * @param array|\ArrayAccess $guards
     *
     * @return bool
     */
    public function callGuards($object, $guards);
}
