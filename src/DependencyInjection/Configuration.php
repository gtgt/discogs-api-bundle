<?php

declare(strict_types = 1);

namespace DiscogsApiBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('discogs_api');

        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('user_agent')
                    ->isRequired()
                    ->info('User-Agent string for API requests. Must be unique to your application.')
                ->end()
                ->scalarNode('base_url')
                    ->defaultValue('https://api.discogs.com')
                    ->info('Discogs API base URL.')
                ->end()
                ->integerNode('timeout')
                    ->defaultValue(30)
                    ->info('HTTP request timeout in seconds.')
                ->end()
                ->arrayNode('user_token')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('token')->defaultNull()->end()
                    ->end()
                ->end()
                ->arrayNode('oauth1')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('consumer_key')->defaultNull()->end()
                        ->scalarNode('consumer_secret')->defaultNull()->end()
                        ->scalarNode('token')->defaultNull()->end()
                        ->scalarNode('token_secret')->defaultNull()->end()
                        ->scalarNode('callback_url')->defaultNull()->end()
                    ->end()
                ->end()
                ->arrayNode('cache')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')->defaultFalse()->end()
                        ->scalarNode('pool')->defaultValue('cache.app')->end()
                        ->arrayNode('ttl')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->integerNode('artists')->defaultValue(3600)->end()
                                ->integerNode('releases')->defaultValue(1800)->end()
                                ->integerNode('masters')->defaultValue(3600)->end()
                                ->integerNode('labels')->defaultValue(3600)->end()
                                ->integerNode('collection')->defaultValue(300)->end()
                                ->integerNode('wantlist')->defaultValue(300)->end()
                                ->integerNode('marketplace')->defaultValue(60)->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->booleanNode('enable_rate_limit_header')
                    ->defaultTrue()
                    ->info('Track rate limit headers.')
                ->end()
                ->booleanNode('retry_on_rate_limit')
                    ->defaultFalse()
                    ->info('Auto-retry after rate limit resets.')
                ->end()
                ->integerNode('max_retries')
                    ->defaultValue(3)
                    ->info('Maximum number of retries for failed requests.')
                ->end()
                ->booleanNode('dispatch_events')
                    ->defaultFalse()
                    ->info('Enable Symfony event dispatching for API calls.')
                ->end()
            ->end();

        return $treeBuilder;
    }
}
