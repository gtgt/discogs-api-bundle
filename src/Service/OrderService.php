<?php

declare(strict_types=1);

namespace Tamash\DiscogsApiBundle\Service;

use Tamash\DiscogsApiBundle\Client\Request\RequestHandler;
use Tamash\DiscogsApiBundle\Model\Marketplace\{Order, OrderMessage};
use Tamash\DiscogsApiBundle\Client\Response\PaginatedResponse;

class OrderService
{
    private RequestHandler $requestHandler;
    private string $baseUrl;

    public function __construct(RequestHandler $requestHandler, string $baseUrl = 'https://api.discogs.com')
    {
        $this->requestHandler = $requestHandler;
        $this->baseUrl = $baseUrl;
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

    public function updateOrder(string $orderId, string $status, array $options = []): Order
    {
        $url = sprintf('%s/marketplace/orders/%s', $this->baseUrl, $orderId);
        $body = array_merge(['status' => $status], $options);

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
