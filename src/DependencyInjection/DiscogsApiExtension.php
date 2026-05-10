<?php

declare(strict_types=1);

namespace Tamash\DiscogsApiBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\{ContainerBuilder, Extension};
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\Config\FileLocator;
use Tamash\DiscogsApiBundle\Client\{Authenticator\AuthenticatorInterface, Authenticator\UserTokenAuthenticator, Authenticator\OAuth1Authenticator};
use Tamash\DiscogsApiBundle\Client\{DiscogsClient, Request\RequestHandler};
use Tamash\DiscogsApiBundle\Service\{
    ArtistService,
    ReleaseService,
    MasterService,
    LabelService,
    UserService,
    CollectionService,
    WantlistService,
    MarketplaceService,
    InventoryService,
    OrderService,
    SearchService
};
use Symfony\Contracts\HttpClient\HttpClientInterface;

class DiscogsApiExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        // Register configuration as a parameter
        $container->setParameter('discogs_api.config', $config);

        // Load services
        $loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/../'));
        $loader->load('services.php');
    }
}
