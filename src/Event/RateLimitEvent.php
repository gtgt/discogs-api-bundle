<?php

declare(strict_types=1);

namespace Tamash\DiscogsApiBundle\Event;

use Tamash\DiscogsApiBundle\Exception\RateLimitException;
use Symfony\Contracts\EventDispatcher\Event;

class RateLimitEvent extends Event
{
    private ?int $retryAfter;

    public function __construct(?int $retryAfter)
    {
        $this->retryAfter = $retryAfter;
    }

    public function getRetryAfter(): ?int
    {
        return $this->retryAfter;
    }
}
