<?php

declare(strict_types=1);

namespace Tamash\DiscogsApiBundle\Client\Request;

use Tamash\DiscogsApiBundle\Client\Authenticator\AuthenticatorInterface;
use Tamash\DiscogsApiBundle\Exception\{RateLimitException, AuthenticationException, NotFoundException, ValidationException, DiscogsApiException};
use Tamash\DiscogsApiBundle\Event;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\HttpClient\{HttpClientInterface, Exception\TransportExceptionInterface};
use Symfony\Contracts\HttpClient\ResponseInterface;

class RequestHandler
{
    private HttpClientInterface $httpClient;
    private ?AuthenticatorInterface $authenticator;
    private ?EventDispatcherInterface $dispatcher;
    private string $userAgent;
    private bool $enableRateLimitHeader;
    private int $maxRetries;

    public function __construct(
        HttpClientInterface $httpClient,
        ?AuthenticatorInterface $authenticator,
        ?EventDispatcherInterface $dispatcher,
        string $userAgent,
        bool $enableRateLimitHeader = true,
        int $maxRetries = 3
    ) {
        $this->httpClient = $httpClient;
        $this->authenticator = $authenticator;
        $this->dispatcher = $dispatcher;
        $this->userAgent = $userAgent;
        $this->enableRateLimitHeader = $enableRateLimitHeader;
        $this->maxRetries = $maxRetries;
    }

    public function request(string $method, string $url, array $options = []): ResponseInterface
    {
        $attempt = 0;

        while (true) {
            $attempt++;

            // Apply authentication if available
            $requestOptions = $options;
            if ($this->authenticator) {
                $this->authenticator->authenticate($this->httpClient, $url, $requestOptions);
            }

            // Set User-Agent
            $requestOptions['headers'] = array_merge($requestOptions['headers'] ?? [], [
                'User-Agent' => $this->userAgent,
            ]);

            // Dispatch before event
            if ($this->dispatcher) {
                $event = new Event\RequestBeforeEvent($method, $url, $requestOptions);
                $this->dispatcher->dispatch($event, Event\DiscogsApiEvents::REQUEST_BEFORE);
            }

            try {
                $response = $this->httpClient->request($method, $url, $requestOptions);

                // Dispatch after event
                if ($this->dispatcher) {
                    $event = new Event\ResponseEvent($response);
                    $this->dispatcher->dispatch($event, Event\DiscogsApiEvents::REQUEST_AFTER);
                }

                $statusCode = $response->getStatusCode();

                // Handle rate limiting
                if ($statusCode === 429) {
                    $retryAfter = $this->parseRetryAfter($response);
                    if ($this->dispatcher) {
                        $event = new Event\RateLimitEvent($retryAfter);
                        $this->dispatcher->dispatch($event, Event\DiscogsApiEvents::RATE_LIMIT_EXCEEDED);
                    }

                    if ($attempt < $this->maxRetries && $retryAfter !== null) {
                        sleep(min($retryAfter, 60));
                        continue;
                    }

                    throw new RateLimitException($response, $retryAfter);
                }

                // Handle other errors - throw, no retry
                if ($statusCode >= 400) {
                    $this->handleErrorResponse($response, $statusCode);
                }

                return $response;

            } catch (RateLimitException $e) {
                if ($attempt >= $this->maxRetries) {
                    throw $e;
                }
                // continue to retry
            } catch (TransportExceptionInterface $e) {
                // Network errors - retry with backoff
                if ($attempt >= $this->maxRetries) {
                    throw $e;
                }
                usleep(100000 * $attempt);
            }
        }
    }

    private function handleErrorResponse(ResponseInterface $response, int $statusCode): void
    {
        $content = $response->getContent(false);

        switch ($statusCode) {
            case 401:
            case 403:
                $exception = new AuthenticationException($response, 'Authentication failed: ' . $content);
                break;
            case 404:
                $exception = new NotFoundException($response, 'Resource not found');
                break;
            case 400:
                $errors = [];
                try {
                    $data = $response->toArray();
                    $errors = $data['message'] ?? $data['errors'] ?? [];
                } catch (\Throwable) {}
                $exception = new ValidationException($response, (array)$errors);
                break;
            default:
                $exception = new DiscogsApiException(
                    'API error: ' . $content,
                    $response,
                    $statusCode
                );
                break;
        }

        // Dispatch error event
        if ($this->dispatcher) {
            $event = new Event\ErrorEvent($exception);
            $this->dispatcher->dispatch($event, Event\DiscogsApiEvents::ERROR);
        }

        throw $exception;
    }

    private function parseRetryAfter(ResponseInterface $response): ?int
    {
        if ($this->enableRateLimitHeader) {
            $retryAfter = $response->getHeaders()->get('X-Ratelimit-Reset')[0] ?? null;
            if ($retryAfter) {
                $timestamp = (int)($retryAfter ?? 0);
                $now = time();
                $diff = $timestamp - $now;
                return $diff > 0 ? $diff : null;
            }
        }

        $retryAfterHeader = $response->getHeaders()->get('Retry-After')[0] ?? null;
        return $retryAfterHeader !== null ? (int)$retryAfterHeader : null;
    }

    public function get(string $url, array $options = []): ResponseInterface
    {
        return $this->request('GET', $url, $options);
    }

    public function post(string $url, array $options = []): ResponseInterface
    {
        return $this->request('POST', $url, $options);
    }

    public function put(string $url, array $options = []): ResponseInterface
    {
        return $this->request('PUT', $url, $options);
    }

    public function delete(string $url, array $options = []): ResponseInterface
    {
        return $this->request('DELETE', $url, $options);
    }
}
