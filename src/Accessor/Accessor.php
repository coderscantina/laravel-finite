<?php

namespace CodersCantina\LaravelFinite\Accessor;

use ArrayAccess;

interface Accessor
{
    public function setState(object $object, string $state): self;

    public function getState(object $object): ?string;

    public function applyProperties(object $object, array $properties): self;

    public function callGuards(object $object, array|ArrayAccess $guards): bool;
}
