<?php

declare(strict_types=1);

namespace DiscogsApiBundle\Service;

use DiscogsApiBundle\Client\Request\RequestHandler;
use DiscogsApiBundle\Model\User;

class UserService {
    private RequestHandler $requestHandler;

    private string $baseUrl;

    public function __construct(RequestHandler $requestHandler, string $baseUrl = 'https://api.discogs.com')
    {
        $this->requestHandler = $requestHandler;
        $this->baseUrl = $baseUrl;
    }

    public function getIdentity(): User
    {
        $url = $this->baseUrl.'/oauth/identity';
        $response = $this->requestHandler->get($url);
        $data = $response->toArray(false);

        return User::fromArray($data);
    }

    public function getUser(string $username): User
    {
        $url = sprintf('%s/users/%s', $this->baseUrl, rawurlencode($username));
        $response = $this->requestHandler->get($url);
        $data = $response->toArray(false);

        return User::fromArray($data);
    }
}
