<?php

namespace CodersCantina\LaravelFinite\Accessor;

use ArrayAccess;

class FluentAccessor implements Accessor
{
    public function getState(object $object): ?string
    {
        return $object['state'];
    }

    public function applyProperties(object $object, array $properties): self
    {
        foreach ($properties as $name => $value) {
            $object[$name] = $value;
        }

        return $this;
    }

    public function setState(object $object, string $state): self
    {
        $object['state'] = $state;

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
