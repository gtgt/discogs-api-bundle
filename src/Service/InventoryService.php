<?php

declare(strict_types=1);

namespace Tamash\DiscogsApiBundle\Service;

use Tamash\DiscogsApiBundle\Client\Request\RequestHandler;
use Tamash\DiscogsApiBundle\Model\Marketplace\Listing;
use Tamash\DiscogsApiBundle\Client\Response\PaginatedResponse;

class InventoryService
{
    private RequestHandler $requestHandler;
    private string $baseUrl;

    public function __construct(RequestHandler $requestHandler, string $baseUrl = 'https://api.discogs.com')
    {
        $this->requestHandler = $requestHandler;
        $this->baseUrl = $baseUrl;
    }

    /**
     * Get all inventory listings
     *
     * @param string|null $username 'me' for authenticated user
     * @param string|null $status Filter by status: 'For Sale', 'Draft', 'Sold', 'Expired', 'Cancelled'
     */
    public function getInventory(?string $username = null, array $options = []): PaginatedResponse
    {
        $username = $username ?? 'me';
        $url = sprintf('%s/inventory', $this->baseUrl);
        $query = array_merge(['username' => $username], $options);

        $response = $this->requestHandler->get($url, ['query' => $query]);
        $data = $response->toArray(false);

        return \Tamash\DiscogsApiBundle\Client\Request\RequestFactory::createPaginatedResponse($data, $response);
    }

    /**
     * Get a single listing
     */
    public function getListing(int|string $listingId, ?string $currency = null): Listing
    {
        $url = sprintf('%s/inventory/listings/%s', $this->baseUrl, $listingId);
        $options = [];
        if ($currency) {
            $options['query'] = ['curr_abbr' => $currency];
        }
        $response = $this->requestHandler->get($url, $options);
        $data = $response->toArray(false);

        return Listing::fromArray($data);
    }

    /**
     * Create a listing
     */
    public function createListing(int $releaseId, string $condition, float $price, array $options = []): Listing
    {
        $url = $this->baseUrl . '/inventory/listings';
        $body = array_merge([
            'release_id' => $releaseId,
            'condition' => $condition,
            'price' => $price,
        ], $options);

        $response = $this->requestHandler->post($url, ['json' => $body]);
        $data = $response->toArray(false);

        return Listing::fromArray($data);
    }

    /**
     * Update a listing
     */
    public function updateListing(int|string $listingId, string $condition, float $price, array $options = []): Listing
    {
        $url = sprintf('%s/inventory/listings/%s', $this->baseUrl, $listingId);
        $body = array_merge([
            'condition' => $condition,
            'price' => $price,
        ], $options);

        $response = $this->requestHandler->put($url, ['json' => $body]);
        $data = $response->toArray(false);

        return Listing::fromArray($data);
    }

    /**
     * Delete a listing
     */
    public function deleteListing(int|string $listingId): void
    {
        $url = sprintf('%s/inventory/listings/%s', $this->baseUrl, $listingId);
        $response = $this->requestHandler->delete($url);
        $response->getStatusCode(); // 204
    }
}
