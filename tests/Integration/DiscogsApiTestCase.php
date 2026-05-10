<?php

// Test bootstrap for integration tests

use Tests\App\Kernel;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase as BaseKernelTestCase;

abstract class IntegrationTestCase extends BaseKernelTestCase
{
    protected static ?Kernel $kernel = null;

    protected function setUp(): void
    {
        static::bootKernel();
    }

    public static function bootKernel(array $options = []): Kernel
    {
        if (null === static::$kernel) {
            static::$kernel = new Kernel('test', true);
        }

        return static::$kernel;
    }
}
