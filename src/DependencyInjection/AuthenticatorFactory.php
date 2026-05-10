<?php

declare(strict_types=1);

namespace Tamash\DiscogsApiBundle\DependencyInjection;

use Tamash\DiscogsApiBundle\Client\Authenticator\{
    AuthenticatorInterface,
    UserTokenAuthenticator,
    OAuth1Authenticator
};
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use League\OAuth1\Client\Credentials\TokenCredentials;

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
            $oauth = new OAuth1Authenticator([
                'identifier' => $oauthConfig['consumer_key'],
                'secret' => $oauthConfig['consumer_secret'],
                'callback_uri' => $oauthConfig['callback_url'],
            ]);

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
