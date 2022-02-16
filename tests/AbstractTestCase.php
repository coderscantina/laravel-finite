<?php namespace Neon\Finite;

use GrahamCampbell\TestBench\AbstractPackageTestCase;

class AbstractTestCase extends AbstractPackageTestCase
{
    protected function getServiceProviderClass()
    {
        return ServiceProvider::class;
    }
}
