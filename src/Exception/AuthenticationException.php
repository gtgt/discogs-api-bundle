<?php

declare(strict_types = 1);

namespace DiscogsApiBundle\Exception;

use Symfony\Contracts\HttpClient\ResponseInterface;

class AuthenticationException extends DiscogsApiException
{
    public function __construct(ResponseInterface $response, string $message = 'Authentication failed')
    {
        parent::__construct($message, $response, $response->getStatusCode());
    }
}
