<?php

declare(strict_types=1);

namespace Tamash\DiscogsApiBundle\Event;

use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\EventDispatcher\Event;

class ResponseEvent extends Event
{
    private ResponseInterface $response;

    public function __construct(ResponseInterface $response)
    {
        $this->response = $response;
    }

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }
}
