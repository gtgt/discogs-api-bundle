<?php

declare(strict_types=1);

namespace Tamash\DiscogsApiBundle\Service;

use Tamash\DiscogsApiBundle\Client\Request\RequestHandler;
use Tamash\DiscogsApiBundle\Model\Collection\CollectionItem;
use Tamash\DiscogsApiBundle\Client\Response\PaginatedResponse;

class WantlistService
{
    private RequestHandler $requestHandler;
    private string $baseUrl;

    public function __construct(RequestHandler $requestHandler, string $baseUrl = 'https://api.discogs.com')
    {
        $this->requestHandler = $requestHandler;
        $this->baseUrl = $baseUrl;
    }

    public function getWantlist(string $username, array $options = []): PaginatedResponse
    {
        $url = sprintf('%s/users/%s/wantlist', $this->baseUrl, rawurlencode($username));
        $response = $this->requestHandler->get($url, ['query' => $options]);
        $data = $response->toArray(false);

        // Map items to CollectionItem objects for consistency
        $items = array_map(fn($item) => CollectionItem::fromArray($item), $data['releases'] ?? $data);
        return new PaginatedResponse(
            $items,
            (int)($data['pagination']['page'] ?? 1),
            (int)($data['pagination']['pages'] ?? 1),
            (int)($data['pagination']['per_page'] ?? count($items)),
            $data['pagination']['urls']['next'] ?? null,
            $data['pagination']['urls']['previous'] ?? null
        );
    }

    public function addToWantlist(string $username, int $releaseId, array $data = []): void
    {
        $url = sprintf('%s/users/%s/wantlist', $this->baseUrl, rawurlencode($username));
        $body = array_merge(['release_id' => $releaseId], $data);

        $response = $this->requestHandler->post($url, [
            'json' => $body
        ]);
        $response->getStatusCode(); // Should be 201
    }

    public function removeFromWantlist(string $username, int $releaseId): void
    {
        $url = sprintf('%s/users/%s/wantlist/%d', $this->baseUrl, rawurlencode($username), $releaseId);
        $response = $this->requestHandler->delete($url);
        $response->getStatusCode(); // Should be 204
    }

    public function updateWantlistItem(string $username, int $releaseId, array $data): void
    {
        $url = sprintf('%s/users/%s/wantlist/%d', $this->baseUrl, rawurlencode($username), $releaseId);
        $response = $this->requestHandler->put($url, [
            'json' => $data
        ]);
        $response->getStatusCode(); // Should be 200
    }
}
