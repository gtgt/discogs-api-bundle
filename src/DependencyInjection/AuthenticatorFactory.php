<?php

declare(strict_types = 1);

namespace DiscogsApiBundle\DependencyInjection;

use DiscogsApiBundle\Client\Authenticator\AuthenticatorInterface;
use DiscogsApiBundle\Client\Authenticator\OAuth1Authenticator;
use DiscogsApiBundle\Client\Authenticator\UserTokenAuthenticator;
use League\OAuth1\Client\Credentials\TokenCredentials;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class AuthenticatorFactory
{
    public static function create(array $config, ?EventDispatcherInterface $dispatcher): AuthenticatorInterface
    {
        $userToken = $config['user_token']['token'] ?? null;
        if ($userToken) {
            return new UserTokenAuthenticator($userToken);
        }

        $oauthConfig = $config['oauth1'];
        if ($oauthConfig['consumer_key'] && $oauthConfig['consumer_secret']) {
            $oauth = new OAuth1Authenticator(
                $oauthConfig['consumer_key'],
                $oauthConfig['consumer_secret'],
                $oauthConfig['callback_url'],
            );

            // If token credentials exist, set them
            if ($oauthConfig['token'] && $oauthConfig['token_secret']) {
                $tokenCredentials = new TokenCredentials();
                $tokenCredentials->setIdentifier($oauthConfig['token']);
                $tokenCredentials->setSecret($oauthConfig['token_secret']);
                $oauth->setTokenCredentials($tokenCredentials);
            }

            return $oauth;
        }

        throw new \RuntimeException('Either user_token or oauth1.consumer_key/secret must be configured');
    }
}
