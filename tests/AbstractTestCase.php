<?php

namespace CodersCantina\LaravelFinite;

use GrahamCampbell\TestBench\AbstractPackageTestCase;

class AbstractTestCase extends AbstractPackageTestCase
{
    protected static function getServiceProviderClass(): string
    {
        return ServiceProvider::class;
    }
}
