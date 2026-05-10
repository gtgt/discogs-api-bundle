<?php

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Tamash\DiscogsApiBundle\Client\{
    Authenticator\AuthenticatorInterface,
    Request\RequestHandler,
    DiscogsClient
};
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
use Tamash\DiscogsApiBundle\DependencyInjection\AuthenticatorFactory;

return static function (ContainerConfigurator $c) {
    $services = $c->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure()
        ->public(false);

    // Authenticator factory
    $services->set('discogs_api.authenticator')
        ->factory([AuthenticatorFactory::class, 'create'])
        ->arg('$config', '%discogs_api.config%');

    $services->set(AuthenticatorInterface::class)
        ->alias('discogs_api.authenticator', 'discogs_api.authenticator');

    // Request Handler
    $services->set(RequestHandler::class)
        ->arg('$httpClient', service(HttpClientInterface::class))
        ->arg('$authenticator', service('discogs_api.authenticator'))
        ->arg('$dispatcher', service('event_dispatcher')->nullOnInvalid())
        ->arg('$userAgent', parameter('discogs_api.config')['user_agent'])
        ->arg('$enableRateLimitHeader', parameter('discogs_api.config')['enable_rate_limit_header'])
        ->arg('$maxRetries', parameter('discogs_api.config')['max_retries']);

    // Services (public for direct access if needed)
    $services->set(ArtistService::class)->public();
    $services->set(ReleaseService::class)->public();
    $services->set(MasterService::class)->public();
    $services->set(LabelService::class)->public();
    $services->set(UserService::class)->public();
    $services->set(CollectionService::class)->public();
    $services->set(WantlistService::class)->public();
    $services->set(MarketplaceService::class)->public();
    $services->set(InventoryService::class)->public();
    $services->set(OrderService::class)->public();
    $services->set(SearchService::class)->public();

    // Facade
    $services->set(DiscogsClient::class)
        ->arg('$requestHandler', service(RequestHandler::class))
        ->arg('$authenticator', service('discogs_api.authenticator'))
        ->arg('$userAgent', parameter('discogs_api.config')['user_agent'])
        ->arg('$baseUrl', parameter('discogs_api.config')['base_url'])
        ->public();
};
