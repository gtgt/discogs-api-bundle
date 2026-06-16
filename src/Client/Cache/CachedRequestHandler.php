<?php

declare(strict_types=1);

namespace DiscogsApiBundle\Client\Cache;

use DiscogsApiBundle\Client\Request\RequestHandler;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class CachedRequestHandler extends RequestHandler {
    private ?CacheItemPoolInterface $cachePool;

    private array $ttlConfig;

    private array $endpointPatterns;

    public function __construct(
        RequestHandler $innerHandler,
        ?CacheItemPoolInterface $cachePool,
        array $ttlConfig = [],
        ?array $endpointPatterns = null
    )
    {
        parent::__construct(
            $innerHandler->getHttpClient(),
            $innerHandler->getAuthenticator(),
            $innerHandler->getDispatcher(),
            $innerHandler->getUserAgent(),
            $innerHandler->isRateLimitHeaderEnabled(),
            $innerHandler->getMaxRetries()
        );

        $this->cachePool = $cachePool;
        $this->ttlConfig = $ttlConfig;
        $this->endpointPatterns = $endpointPatterns ?? $this->getDefaultPatterns();
    }

    private function getDefaultPatterns(): array
    {
        return [
            '#^/artists/#'            => $this->ttlConfig['artists'] ?? 3600,
            '#^/masters/#'            => $this->ttlConfig['masters'] ?? 3600,
            '#^/labels/#'             => $this->ttlConfig['labels'] ?? 3600,
            '#^/releases/#'           => $this->ttlConfig['releases'] ?? 1800,
            '#^/users/.*/collection#' => $this->ttlConfig['collection'] ?? 300,
            '#^/users/.*/wantlist#'   => $this->ttlConfig['wantlist'] ?? 300,
            '#^/inventory#'           => $this->ttlConfig['marketplace'] ?? 60,
            '#^/marketplace/orders#'  => $this->ttlConfig['marketplace'] ?? 60,
            '#^/database/search#'     => 60, // Short cache for search
        ];
    }

    public function request(string $method, string $url, array $options = []): ResponseInterface
    {
        // Only cache GET requests
        if ($method !== 'GET' || $this->cachePool === null) {
            return $this->delegateRequest($method, $url, $options);
        }

        // Generate cache key
        $cacheKey = $this->generateCacheKey($method, $url, $options);

        // Try cache
        $item = $this->cachePool->getItem($cacheKey);
        if ($item->isHit()) {
            return $item->get();
        }

        // Execute request
        $response = $this->delegateRequest($method, $url, $options);

        // Cache successful GET responses
        if ($response->getStatusCode() === 200) {
            $ttl = $this->getTtlForEndpoint($url);
            $item->set($response);
            $item->expiresAfter($ttl);
            $this->cachePool->save($item);
        }

        return $response;
    }

    private function delegateRequest(string $method, string $url, array $options): ResponseInterface
    {
        return parent::request($method, $url, $options);
    }

    private function generateCacheKey(string $method, string $url, array $options): string
    {
        $keyParts = [
            $method,
            $url,
            http_build_query($options['query'] ?? []),
            $options['body'] ?? null,
        ];

        return 'discogs_api:'.md5(implode('|', $keyParts));
    }

    private function getTtlForEndpoint(string $url): int
    {
        $path = parse_url($url, PHP_URL_PATH);

        foreach ($this->endpointPatterns as $pattern => $ttl) {
            if (preg_match($pattern, $path)) {
                return $ttl;
            }
        }

        // Default TTL
        return 300;
    }

    public function post(string $url, array $options = []): ResponseInterface
    {
        $response = $this->delegateRequest('POST', $url, $options);
        $this->invalidateRelatedCache($url, $options);

        return $response;
    }

    private function invalidateRelatedCache(string $url, array $options): void
    {
        if ($this->cachePool === null) {
            return;
        }

        // Extract resource IDs from URL for targeted invalidation
        $pattern = '#/(artists|releases|masters|labels|users)/(\d+)#';
        if (preg_match($pattern, $url, $matches)) {
            $resourceType = $matches[1];
            $resourceId = $matches[2];

            // Invalidate cache for this specific resource
            $keyPattern = sprintf('discogs_api:GET:%s/%s*', $resourceType, $resourceId);
            // PSR-6 doesn't support pattern deletion directly; we'd need to track keys
            // For now, clear entire cache on writes as a safe operation
            $this->cachePool->clear();
        }
    }

    public function put(string $url, array $options = []): ResponseInterface
    {
        $response = $this->delegateRequest('PUT', $url, $options);
        $this->invalidateRelatedCache($url, $options);

        return $response;
    }

    public function delete(string $url, array $options = []): ResponseInterface
    {
        $response = $this->delegateRequest('DELETE', $url, $options);
        $this->invalidateRelatedCache($url, $options);

        return $response;
    }
}
