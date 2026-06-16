<?php

declare(strict_types=1);

namespace DiscogsApiBundle\Exception;

use Symfony\Contracts\HttpClient\ResponseInterface;

class NotFoundException extends DiscogsApiException {
    public function __construct(ResponseInterface $response, string $resource = 'resource')
    {
        parent::__construct("{$resource} not found", $response, 404);
    }
}
