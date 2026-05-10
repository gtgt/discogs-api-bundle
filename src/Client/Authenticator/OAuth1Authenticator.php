<?php

declare(strict_types=1);

namespace Tamash\DiscogsApiBundle\Client\Authenticator;

use League\OAuth1\Client\Server\Server as BaseServer;
use League\OAuth1\Client\Credentials\{CredentialsInterface, TokenCredentials};
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class OAuth1Authenticator extends BaseServer implements AuthenticatorInterface
{
    private ?TokenCredentials $tokenCredentials = null;

    public function __construct(array $config)
    {
        parent::__construct($config);
    }

    public function setTokenCredentials(TokenCredentials $credentials): void
    {
        $this->tokenCredentials = $credentials;
    }

    public function getTokenCredentials(): ?TokenCredentials
    {
        return $this->tokenCredentials;
    }

    public function authenticate(HttpClientInterface $client, string $url, array &$options = []): void
    {
        if (null === $this->tokenCredentials) {
            throw new \RuntimeException('OAuth token credentials not set. Complete OAuth flow first.');
        }

        // Get signature for this request
        $signature = $this->getSignature($this->tokenCredentials, 'POST', $url, $options['body'] ?? []);

        // Add OAuth headers
        $headers = [
            'Authorization: OAuth ' .
            'oauth_consumer_key="' . $this->identifier . '", ' .
            'oauth_token="' . $this->tokenCredentials->getIdentifier() . '", ' .
            'oauth_signature_method="PLAINTEXT", ' .
            'oauth_signature="' . rawurlencode($this->secret) . '&' . rawurlencode($this->tokenCredentials->getSecret()) . '", ' .
            'oauth_timestamp="' . time() . '", ' .
            'oauth_nonce="' . bin2hex(random_bytes(16)) . '", ' .
            'oauth_version="1.0"'
        ];

        $options['headers'] = array_merge($options['headers'] ?? [], $headers);
    }

    public function getAuthorizationHeader(): ?string
    {
        if (null === $this->tokenCredentials) {
            return null;
        }

        return 'OAuth ' .
            'oauth_consumer_key="' . $this->identifier . '", ' .
            'oauth_token="' . $this->tokenCredentials->getIdentifier() . '", ' .
            'oauth_signature_method="PLAINTEXT", ' .
            'oauth_signature="' . rawurlencode($this->secret) . '&' . rawurlencode($this->tokenCredentials->getSecret()) . '", ' .
            'oauth_timestamp="' . time() . '", ' .
            'oauth_nonce="' . bin2hex(random_bytes(16)) . '", ' .
            'oauth_version="1.0"';
    }

    // Override to use Discogs-specific URLs
    protected function getTemporaryCredentialsUrl(): string
    {
        return 'https://api.discogs.com/oauth/request_token';
    }

    protected function getAuthorizationUrl($temporaryCredentials): string
    {
        return 'https://www.discogs.com/oauth/authorize?' . http_build_query([
            'oauth_token' => $temporaryCredentials['oauth_token']
        ]);
    }

    protected function getAccessTokenUrl(): string
    {
        return 'https://api.discogs.com/oauth/access_token';
    }

    protected function getBaseUrl(): string
    {
        return 'https://api.discogs.com';
    }

    protected function getBaseAuthorizationUrl(): string
    {
        return 'https://www.discogs.com/oauth';
    }

    protected function contentType(): string
    {
        return 'application/x-www-form-urlencoded';
    }
}
