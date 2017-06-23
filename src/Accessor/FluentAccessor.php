<?php namespace Neon\Finite\Accessor;

use Illuminate\Support\Fluent;

class FluentAccessor implements Accessor
{
    /**
     * @param Fluent $object
     *
     * @return string|null
     */
    public function getState($object)
    {
        return $object['state'];
    }

    /**
     * @param Fluent $object
     * @param array $properties
     *
     * @return $this
     */
    public function applyProperties($object, $properties)
    {
        foreach ($properties as $name => $value) {
            $object[$name] = $value;
        }

        return $this;
    }

    /**
     * @param Fluent $object
     * @param string $state
     *
     * @return FluentAccessor
     */
    public function setState($object, $state)
    {
        $object['state'] = $state;

        return $this;
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
