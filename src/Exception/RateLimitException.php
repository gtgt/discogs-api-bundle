<?php

declare(strict_types=1);

namespace Tamash\DiscogsApiBundle\Exception;

use Symfony\Contracts\HttpClient\ResponseInterface;

class RateLimitException extends DiscogsApiException
{
    private ?int $retryAfter;

    public function __construct(ResponseInterface $response, ?int $retryAfter = null)
    {
        $this->retryAfter = $retryAfter;
        $message = 'Rate limit exceeded' . ($retryAfter ? ", retry after {$retryAfter} seconds" : '');
        parent::__construct($message, $response, 429);
    }

    public function getRetryAfter(): ?int
    {
        return $this->retryAfter;
    }
}
