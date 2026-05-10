<?php

declare(strict_types = 1);

namespace DiscogsApiBundle\Client\Authenticator;

use Symfony\Contracts\HttpClient\HttpClientInterface;

interface AuthenticatorInterface
{
    public function authenticate(HttpClientInterface $client, string $url, array &$options = []): void;

    public function getAuthorizationHeader(): ?string;
}
