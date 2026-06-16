<?php

declare(strict_types=1);

namespace DiscogsApiBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class DiscogsApiExtension extends Extension {
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        // Register configuration as a parameter
        $this->setConfigAsParameter('discogs_api.config', $config, $container);

        // Load services
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../../config'));
        $loader->load('services.yaml');
    }

    private function setConfigAsParameter(string $prefix, array $config, ContainerBuilder $container)
    {
        foreach ($config as $key => $value) {
            is_array($value) ? $this->setConfigAsParameter($prefix.'.'.$key, $value, $container) : $container->setParameter($prefix.'.'.$key, $value);
        }
        $container->setParameter($prefix, $config);
    }
}
