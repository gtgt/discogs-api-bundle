<?php

declare(strict_types = 1);

namespace DiscogsApiBundle\Client\Request;

use DiscogsApiBundle\Client\Authenticator\AuthenticatorInterface;
use DiscogsApiBundle\Event;
use DiscogsApiBundle\Exception\AuthenticationException;
use DiscogsApiBundle\Exception\DiscogsApiException;
use DiscogsApiBundle\Exception\NotFoundException;
use DiscogsApiBundle\Exception\RateLimitException;
use DiscogsApiBundle\Exception\ValidationException;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class RequestHandler
{
    private HttpClientInterface $httpClient;

    private ?AuthenticatorInterface $authenticator;

    private ?EventDispatcherInterface $dispatcher;

    private string $userAgent;

    private bool $enableRateLimitHeader;

    private int $maxRetries;

    private ?CacheItemPoolInterface $cachePool;

    private array $cacheTtl;

    private array $endpointPatterns;

    public function __construct(
        HttpClientInterface $httpClient,
        ?AuthenticatorInterface $authenticator,
        ?EventDispatcherInterface $dispatcher,
        string $userAgent,
        bool $enableRateLimitHeader = true,
        int $maxRetries = 3,
        ?CacheItemPoolInterface $cachePool = null,
        array $cacheTtl = [],
        ?array $endpointPatterns = null
    ) {
        $this->httpClient = $httpClient;
        $this->authenticator = $authenticator;
        $this->dispatcher = $dispatcher;
        $this->userAgent = $userAgent;
        $this->enableRateLimitHeader = $enableRateLimitHeader;
        $this->maxRetries = $maxRetries;
        $this->cachePool = $cachePool;
        $this->cacheTtl = $cacheTtl;
        $this->endpointPatterns = $endpointPatterns ?? $this->getDefaultPatterns();
    }

    private function getDefaultPatterns(): array
    {
        return [
            '#^/artists/#' => $this->cacheTtl['artists'] ?? 3600,
            '#^/masters/#' => $this->cacheTtl['masters'] ?? 3600,
            '#^/labels/#' => $this->cacheTtl['labels'] ?? 3600,
            '#^/releases/#' => $this->cacheTtl['releases'] ?? 1800,
            '#^/users/.*/collection#' => $this->cacheTtl['collection'] ?? 300,
            '#^/users/.*/wantlist#' => $this->cacheTtl['wantlist'] ?? 300,
            '#^/inventory#' => $this->cacheTtl['marketplace'] ?? 60,
            '#^/marketplace/orders#' => $this->cacheTtl['marketplace'] ?? 60,
            '#^/database/search#' => 60,
        ];
    }

    private function prepareRequestOptions(string $method, string $url, array $options): array
    {
        $requestOptions = $options;

        // Apply authentication if available
        if ($this->authenticator) {
            $this->authenticator->authenticate($this->httpClient, $url, $requestOptions);
        }

        // Set User-Agent
        $requestOptions['headers'] = array_merge($requestOptions['headers'] ?? [], [
            'User-Agent' => $this->userAgent,
        ]);

        return $requestOptions;
    }

    private function generateCacheKey(string $method, string $url, array $options): string
    {
        $keyParts = [
            $method,
            $url,
            http_build_query($options['query'] ?? []),
            isset($options['body']) ? json_encode($options['body']) : null,
        ];

        return 'discogs_api:' . md5(implode('|', $keyParts));
    }

    private function getTtlForEndpoint(string $url): int
    {
        $path = parse_url($url, PHP_URL_PATH);
        if ($path === null) {
            return 300;
        }

        foreach ($this->endpointPatterns as $pattern => $ttl) {
            if (preg_match($pattern, $path)) {
                return $ttl;
            }
        }

        return 300;
    }

    public function request(string $method, string $url, array $options = []): ResponseInterface
    {
        // Prepare request options (apply auth, headers) - this is deterministic
        $preparedOptions = $this->prepareRequestOptions($method, $url, $options);

        // Caching for GET requests only
        if ($method === 'GET' && $this->cachePool !== null) {
            $cacheKey = $this->generateCacheKey($method, $url, $preparedOptions);
            $cachedItem = $this->cachePool->getItem($cacheKey);
            if ($cachedItem->isHit()) {
                $cached = $cachedItem->get();
                $headers = [];
                foreach ($cached['headers'] as $name => $values) {
                    foreach ((array) $values as $value) {
                        $headers[] = $name . ': ' . $value;
                    }
                }

                return new MockResponse(
                    $cached['content'],
                    ['http_code' => $cached['status'], 'response_headers' => $headers]
                );
            }
        }

        $attempt = 0;

        while (true) {
            $attempt++;

            // Dispatch before event
            if ($this->dispatcher) {
                $event = new Event\RequestBeforeEvent($method, $url, $preparedOptions);
                $this->dispatcher->dispatch($event, Event\DiscogsApiEvents::REQUEST_BEFORE);
            }

            try {
                $response = $this->httpClient->request($method, $url, $preparedOptions);

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

                // Cache successful GET responses
                if ($method === 'GET' && $statusCode === 200 && $this->cachePool !== null) {
                    $cachedData = [
                        'content' => $response->getContent(),
                        'status' => $statusCode,
                        'headers' => $response->getHeaders(),
                    ];
                    $cachedItem = $this->cachePool->getItem($cacheKey);
                    $cachedItem->set($cachedData);
                    $cachedItem->expiresAfter($this->getTtlForEndpoint($url));
                    $this->cachePool->save($cachedItem);
                }

                // Invalidate cache on write operations (non-GET)
                if ($method !== 'GET' && $this->cachePool !== null) {
                    $this->cachePool->clear();
                }

                return $response;

            } catch (RateLimitException $e) {
                if ($attempt >= $this->maxRetries) {
                    throw $e;
                }
                // continue to retry
            } catch (TransportExceptionInterface $e) {
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
                } catch (\Throwable) {
                }
                $exception = new ValidationException($response, (array) $errors);
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
        $headers = $response->getHeaders();

        if ($this->enableRateLimitHeader) {
            $retryAfter = $headers['x-ratelimit-reset'][0] ?? null;
            if ($retryAfter) {
                $timestamp = (int) ($retryAfter ?? 0);
                $now = time();
                $diff = $timestamp - $now;

                return $diff > 0 ? $diff : null;
            }
        }

        $retryAfterHeader = $headers['retry-after'][0] ?? null;

        return $retryAfterHeader !== null ? (int) $retryAfterHeader : null;
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

    // Getter for cache pool (if needed)
    public function getCachePool(): ?CacheItemPoolInterface
    {
        return $this->cachePool;
    }

    protected function getHttpClient(): HttpClientInterface
    {
        return $this->httpClient;
    }

    protected function getAuthenticator(): ?AuthenticatorInterface
    {
        return $this->authenticator;
    }

    protected function getDispatcher(): ?EventDispatcherInterface
    {
        return $this->dispatcher;
    }

    protected function getUserAgent(): string
    {
        return $this->userAgent;
    }

    protected function isRateLimitHeaderEnabled(): bool
    {
        return $this->enableRateLimitHeader;
    }

    protected function getMaxRetries(): int
    {
        return $this->maxRetries;
    }
}
