<?php

declare(strict_types=1);

namespace Tamash\DiscogsApiBundle\Exception;

use Symfony\Contracts\HttpClient\ResponseInterface;

class ValidationException extends DiscogsApiException
{
    private array $errors;

    public function __construct(ResponseInterface $response, array $errors = [])
    {
        $this->errors = $errors;
        $message = 'Validation failed' . (!empty($errors) ? ': ' . json_encode($errors) : '');
        parent::__construct($message, $response, 400);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
