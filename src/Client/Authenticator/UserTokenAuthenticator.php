<?php

declare(strict_types=1);

namespace Tamash\DiscogsApiBundle\Client\Authenticator;

use Symfony\Contracts\HttpClient\HttpClientInterface;

final class UserTokenAuthenticator implements AuthenticatorInterface
{
    private string $token;

    public function __construct(string $token)
    {
        $this->token = $token;
    }

    public function authenticate(HttpClientInterface $client, string $url, array &$options = []): void
    {
        // Add token as query parameter
        $separator = (parse_url($url, PHP_URL_QUERY) ? '&' : '?');
        $options['query'] = array_merge($options['query'] ?? [], ['token' => $this->token]);
    }

    public function getAuthorizationHeader(): ?string
    {
        return null;
    }
}
