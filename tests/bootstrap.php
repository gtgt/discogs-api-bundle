<?php

declare(strict_types=1);

use Tamash\DiscogsApiBundle\DiscogsApiBundle;

require_once __DIR__ . '/../vendor/autoload.php';

// Initialize Symfony kernel for functional tests if needed
$kernel = new \Symfony\Component\HttpKernel\Kernel('test', true);
$kernel->boot();
