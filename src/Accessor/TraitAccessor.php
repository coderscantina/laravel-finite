<?php

namespace CodersCantina\LaravelFinite\Accessor;

use ArrayAccess;

class TraitAccessor implements Accessor
{
    public function getState(object $object): ?string
    {
        return $object->getState();
    }

    public function setState(object $object, string $state): self
    {
        $object->setState($state);

        return $this;
    }

    public function applyProperties(object $object, array $properties): self
    {
        $object->applyProperties($properties);

        return $this;
    }

    public function callGuards(object $object, array|ArrayAccess $guards): bool
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
