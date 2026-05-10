<?php

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $c) {
    $c->extension('discogs_api', [
        'user_agent' => 'DiscogsApiBundle/Test',
        'user_token' => [
            'token' => getenv('DISCOGS_USER_TOKEN') ?: 'test_token',
        ],
        // Or for OAuth testing:
        // 'oauth1' => [
        //     'consumer_key' => getenv('DISCOGS_CONSUMER_KEY'),
        //     'consumer_secret' => getenv('DISCOGS_CONSUMER_SECRET'),
        // ],
        'dispatch_events' => true,
        'cache' => [
            'enabled' => false,
        ],
    ]);
};
