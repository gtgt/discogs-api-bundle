<?php

namespace DiscogsApiBundle\Tests\Integration;

// Test bootstrap for integration tests

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase as BaseKernelTestCase;
use Tests\App\Kernel;

abstract class IntegrationTestCase extends BaseKernelTestCase
{
    protected static function getKernelClass(): string
    {
        return Kernel::class;
    }

    protected function setUp(): void
    {
        static::bootKernel(['environment' => 'test', 'debug' => false]);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        // Restore exception handler to what it was before test
        restore_exception_handler();
    }
}
