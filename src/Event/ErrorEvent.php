<?php

declare(strict_types=1);

namespace DiscogsApiBundle\Event;

use DiscogsApiBundle\Exception\DiscogsApiException;
use Symfony\Contracts\EventDispatcher\Event;

class ErrorEvent extends Event {
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
