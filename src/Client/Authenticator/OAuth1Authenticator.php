<?php

declare(strict_types=1);

namespace DiscogsApiBundle\Client\Authenticator;

use League\OAuth1\Client\Credentials\TemporaryCredentials;
use League\OAuth1\Client\Credentials\TokenCredentials;
use League\OAuth1\Client\Server\Server as BaseServer;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class OAuth1Authenticator extends BaseServer implements AuthenticatorInterface {
    private ?TokenCredentials $tokenCredentials = null;

    public function __construct(
        private string $identifier,
        private string $secret,
        private string $callbackUri,
    )
    {
        parent::__construct(['identifier' => $this->identifier, 'secret' => $this->secret, 'callback_uri' => $this->callbackUri]);
    }

    public function setTokenCredentials(TokenCredentials $credentials): void
    {
        $this->tokenCredentials = $credentials;
    }

    public function getStoredTokenCredentials(): ?TokenCredentials
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
            'Authorization: OAuth '.
            'oauth_consumer_key="'.$this->identifier.'", '.
            'oauth_token="'.$this->tokenCredentials->getIdentifier().'", '.
            'oauth_signature_method="PLAINTEXT", '.
            'oauth_signature="'.rawurlencode($this->secret).'&'.rawurlencode($this->tokenCredentials->getSecret()).'", '.
            'oauth_timestamp="'.time().'", '.
            'oauth_nonce="'.bin2hex(random_bytes(16)).'", '.
            'oauth_version="1.0"',
        ];

        $options['headers'] = array_merge($options['headers'] ?? [], $headers);
    }

    public function getAuthorizationHeader(): ?string
    {
        if (null === $this->tokenCredentials) {
            return null;
        }

        return 'OAuth '.
            'oauth_consumer_key="'.$this->identifier.'", '.
            'oauth_token="'.$this->tokenCredentials->getIdentifier().'", '.
            'oauth_signature_method="PLAINTEXT", '.
            'oauth_signature="'.rawurlencode($this->secret).'&'.rawurlencode($this->tokenCredentials->getSecret()).'", '.
            'oauth_timestamp="'.time().'", '.
            'oauth_nonce="'.bin2hex(random_bytes(16)).'", '.
            'oauth_version="1.0"';
    }

    // Override to use Discogs-specific URLs

    public function getAuthorizationUrl($temporaryIdentifier, array $options = []): string
    {
        $token = $temporaryIdentifier instanceof TemporaryCredentials
            ? $temporaryIdentifier->getIdentifier()
            : $temporaryIdentifier;

        return 'https://www.discogs.com/oauth/authorize?'.http_build_query([
                'oauth_token' => $token,
            ]);
    }

    public function urlTemporaryCredentials(): string
    {
        return $this->getTemporaryCredentialsUrl();
    }

    protected function getTemporaryCredentialsUrl(): string
    {
        return 'https://api.discogs.com/oauth/request_token';
    }

    public function urlAuthorization(): string
    {
        return $this->getBaseAuthorizationUrl().'/authorize';
    }

    protected function getBaseAuthorizationUrl(): string
    {
        return 'https://www.discogs.com/oauth';
    }

    public function urlTokenCredentials(): string
    {
        return $this->getAccessTokenUrl();
    }

    // ---- Abstract method implementations required by League\OAuth1\Client\Server\Server ----

    protected function getAccessTokenUrl(): string
    {
        return 'https://api.discogs.com/oauth/access_token';
    }

    public function urlUserDetails(): string
    {
        return $this->getBaseUrl().'/oauth/identity';
    }

    protected function getBaseUrl(): string
    {
        return 'https://api.discogs.com';
    }

    public function userDetails($data, TokenCredentials $tokenCredentials): \League\OAuth1\Client\Server\User
    {
        $user = new \League\OAuth1\Client\Server\User();
        $user->uid = $data['id'] ?? null;
        $user->nickname = $data['username'] ?? null;
        $user->name = $data['name'] ?? null;
        $user->firstName = $data['firstname'] ?? null;
        $user->lastName = $data['lastname'] ?? null;
        $user->email = $data['email'] ?? null;
        $user->location = $data['location'] ?? null;
        $user->description = $data['profile'] ?? null;
        $user->imageUrl = $data['avatar_url'] ?? null;
        $user->urls = $data['resource_url'] ?? [];
        $user->extra = $data;

        return $user;
    }

    public function userUid($data, TokenCredentials $tokenCredentials)
    {
        return $data['id'] ?? null;
    }

    public function userEmail($data, TokenCredentials $tokenCredentials): ?string
    {
        return $data['email'] ?? null;
    }

    public function userScreenName($data, TokenCredentials $tokenCredentials): ?string
    {
        return $data['username'] ?? null;
    }

    protected function contentType(): string
    {
        return 'application/x-www-form-urlencoded';
    }
}
