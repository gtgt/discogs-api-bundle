<?php

declare(strict_types=1);

namespace Tamash\DiscogsApiBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Tamash\DiscogsApiBundle\DependencyInjection\DiscogsApiExtension;

class DiscogsApiBundle extends AbstractBundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }

    public function getContainerExtension(): ?DiscogsApiExtension
    {
        return new DiscogsApiExtension();
    }
}
