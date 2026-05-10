<?php

declare(strict_types=1);

namespace Tamash\DiscogsApiBundle\Event;

use Tamash\DiscogsApiBundle\Exception\DiscogsApiException;
use Symfony\Contracts\EventDispatcher\Event;

class ErrorEvent extends Event
{
    private DiscogsApiException $exception;

    public function __construct(DiscogsApiException $exception)
    {
        $this->exception = $exception;
    }

    public function getException(): DiscogsApiException
    {
        return $this->exception;
    }
}
