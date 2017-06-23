<?php namespace Neon\Finite\Accessor;

use Neon\Finite\StateTrait;

class TraitAccessor implements Accessor
{
    /**
     * @param StateTrait $object
     *
     * @return null|string
     */
    public function getState($object)
    {
        return $object->getState();
    }

    /**
     * @param StateTrait $object
     * @param string $state
     *
     * @return $this
     */
    public function setState($object, $state)
    {
        $object->setState($state);

        return $this;
    }

    /**
     * @param StateTrait $object
     * @param array $properties
     *
     * @return self
     */
    public function applyProperties($object, $properties)
    {
        $object->applyProperties($properties);
    }

    /**
     * @param $object
     * @param \Closure[] $guards
     *
     * @return bool
     */
    public function callGuards($object, $guards)
    {
        $result = true;

        foreach ($guards as $guard) {
            $result &= $guard($object);
            if (!$result) {
                break;
            }
        }

        return $result;
    }
}
