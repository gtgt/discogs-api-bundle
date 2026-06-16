<?php

declare(strict_types=1);

namespace DiscogsApiBundle\Exception;

use Symfony\Contracts\HttpClient\ResponseInterface;

class DiscogsApiException extends \RuntimeException {
    private ResponseInterface $response;

    public function __construct(string $message, ResponseInterface $response, int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->response = $response;
    }

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    public function getStatusCode(): int
    {
        return $this->response->getStatusCode();
    }

    public function getResponseBody(): array
    {
        try {
            return $this->response->toArray();
        } catch (\Throwable) {
            return [];
        }
    }
}
