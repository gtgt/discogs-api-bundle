<?php

declare(strict_types=1);

namespace Tamash\DiscogsApiBundle\Service;

use Tamash\DiscogsApiBundle\Client\Request\RequestHandler;
use Tamash\DiscogsApiBundle\Model\Marketplace\{Listing, Order, OrderMessage};
use Tamash\DiscogsApiBundle\Client\Response\PaginatedResponse;

class MarketplaceService
{
    private RequestHandler $requestHandler;
    private string $baseUrl;

    public function __construct(RequestHandler $requestHandler, string $baseUrl = 'https://api.discogs.com')
    {
        $this->requestHandler = $requestHandler;
        $this->baseUrl = $baseUrl;
    }

    /**
     * Create a new marketplace listing
     *
     * @param int $releaseId The Discogs release ID
     * @param string $condition Condition code: 'Mint', 'Near Mint', 'Very Good Plus', 'Very Good', 'Good Plus', 'Good', 'Fair', 'Poor'
     * @param float $price Selling price
     * @param array $options {
     *     @var string $status 'For Sale', 'Draft', 'Sold', 'Expired', 'Cancelled'
     *     @var string|null $sleeveCondition Condition of sleeve
     *     @var string|null $comments Seller comments
     *     @var bool $allowOffers Allow offers?
     *     @var string|null $externalId Your external ID
     *     @var string|null $location Location
     *     @var float|null $weight Weight in grams
     *     @var int|null $formatQuantity Quantity for this format
     * }
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

    public function deleteListing(int|string $listingId): void
    {
        $url = sprintf('%s/inventory/listings/%s', $this->baseUrl, $listingId);
        $response = $this->requestHandler->delete($url);
        $response->getStatusCode(); // 204
    }

    /**
     * Get user's inventory (all listings)
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

    public function getOrders(array $options = []): PaginatedResponse
    {
        $url = $this->baseUrl . '/marketplace/orders';
        $response = $this->requestHandler->get($url, ['query' => $options]);
        $data = $response->toArray(false);

        return \Tamash\DiscogsApiBundle\Client\Request\RequestFactory::createPaginatedResponse($data, $response);
    }

    public function getOrder(string $orderId): Order
    {
        $url = sprintf('%s/marketplace/orders/%s', $this->baseUrl, $orderId);
        $response = $this->requestHandler->get($url);
        $data = $response->toArray(false);

        return Order::fromArray($data);
    }

    public function updateOrder(string $orderId, string $status, ?string $trackingNumber = null, ?string $trackingUrl = null): Order
    {
        $url = sprintf('%s/marketplace/orders/%s', $this->baseUrl, $orderId);
        $body = ['status' => $status];
        if ($trackingNumber !== null) {
            $body['tracking_number'] = $trackingNumber;
        }
        if ($trackingUrl !== null) {
            $body['tracking_url'] = $trackingUrl;
        }

        $response = $this->requestHandler->put($url, ['json' => $body]);
        $data = $response->toArray(false);

        return Order::fromArray($data);
    }

    public function getOrderMessages(string $orderId, array $options = []): array
    {
        $url = sprintf('%s/marketplace/orders/%s/messages', $this->baseUrl, $orderId);
        $response = $this->requestHandler->get($url, ['query' => $options]);
        $data = $response->toArray(false);

        $messages = [];
        foreach ($data['messages'] ?? [] as $msgData) {
            $messages[] = OrderMessage::fromArray($msgData);
        }
        return $messages;
    }

    public function addOrderMessage(string $orderId, string $message): OrderMessage
    {
        $url = sprintf('%s/marketplace/orders/%s/messages', $this->baseUrl, $orderId);
        $response = $this->requestHandler->post($url, [
            'json' => ['message' => $message]
        ]);
        $data = $response->toArray(false);

        return OrderMessage::fromArray($data);
    }
}
