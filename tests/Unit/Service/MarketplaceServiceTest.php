<?php

namespace DiscogsApiBundle\Tests\Unit\Service;

use DiscogsApiBundle\Tests\Unit\UnitTestCase;
use DiscogsApiBundle\Service\MarketplaceService;
use DiscogsApiBundle\Model\Marketplace\{Listing, Order, OrderMessage};
use DiscogsApiBundle\Client\Request\RequestHandler;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class MarketplaceServiceTest extends UnitTestCase
{
    private HttpClientInterface&MockObject $httpClient;
    private RequestHandler $requestHandler;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->requestHandler = new RequestHandler(
            $this->httpClient,
            null, // authenticator
            null, // dispatcher
            'test-agent',
            false, // enableRateLimitHeader
            0, // maxRetries
            null, // cachePool
        );
    }

    public function testCreateListing(): void
    {
        $data = [
            'id' => 123,
            'release' => ['id' => 456, 'title' => 'Test Release'],
            'status' => 'For Sale',
            'price' => 29.99,
            'currency' => 'USD',
            'condition' => 'Near Mint',
            'allow_offers' => false,
        ];

        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->once())
            ->method('toArray')
            ->willReturn($data);
        $response->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(201);

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with('POST', 'https://api.discogs.com/marketplace/listings', [
                'headers' => ['User-Agent' => 'test-agent'],
                'json' => [
                    'release_id' => 456,
                    'condition' => 'Near Mint',
                    'price' => 29.99,
                    'currency' => 'USD',
                    'allow_offers' => false,
                ]
            ])
            ->willReturn($response);

        $service = new MarketplaceService($this->requestHandler);

        $listing = $service->createListing(456, 'Near Mint', 29.99, [
            'currency' => 'USD',
            'allow_offers' => false,
        ]);

        $this->assertInstanceOf(Listing::class, $listing);
        $this->assertSame(123, $listing->id);
        $this->assertSame('For Sale', $listing->status);
        $this->assertSame(29.99, $listing->price);
    }

    public function testGetListing(): void
    {
        $data = [
            'id' => 789,
            'release' => ['id' => 101, 'title' => 'Another Release'],
            'status' => 'Draft',
            'price' => 15.00,
            'currency' => 'EUR',
            'condition' => 'Good',
            'allow_offers' => true,
        ];

        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->once())
            ->method('toArray')
            ->willReturn($data);
        $response->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(200);

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with('GET', 'https://api.discogs.com/marketplace/listings/789', [
                'headers' => ['User-Agent' => 'test-agent']
            ])
            ->willReturn($response);

        $service = new MarketplaceService($this->requestHandler);

        $listing = $service->getListing(789);

        $this->assertSame(789, $listing->id);
        $this->assertSame('Draft', $listing->status);
    }

    public function testUpdateListing(): void
    {
        $data = [
            'id' => 123,
            'status' => 'For Sale',
            'price' => 39.99,
            'condition' => 'Very Good Plus',
            'currency' => 'EUR',
        ];

        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->once())
            ->method('toArray')
            ->willReturn($data);
        $response->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(200);

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with('PUT', 'https://api.discogs.com/marketplace/listings/123', [
                'headers' => ['User-Agent' => 'test-agent'],
                'json' => [
                    'condition' => 'Very Good Plus',
                    'price' => 39.99,
                    'currency' => 'EUR',
                ]
            ])
            ->willReturn($response);

        $service = new MarketplaceService($this->requestHandler);

        $listing = $service->updateListing(123, 'Very Good Plus', 39.99, ['currency' => 'EUR']);

        $this->assertSame(39.99, $listing->price);
    }

    public function testGetOrder(): void
    {
        $data = [
            'id' => 123,
            'status' => 'paid',
            'buyer' => 'buyer_user',
            'seller' => 'seller_user',
            'total' => 59.99,
            'currency' => 'USD',
            'created' => '2024-01-01T12:00:00',
            'last_activity' => '2024-01-02T12:00:00',
            'items' => [],
        ];

        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->once())
            ->method('toArray')
            ->willReturn($data);
        $response->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(200);

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with('GET', 'https://api.discogs.com/marketplace/orders/123', [
                'headers' => ['User-Agent' => 'test-agent']
            ])
            ->willReturn($response);

        $service = new MarketplaceService($this->requestHandler);

        $order = $service->getOrder(123);

        $this->assertInstanceOf(Order::class, $order);
        $this->assertSame(123, $order->id);
        $this->assertSame('paid', $order->status);
    }

    public function testAddOrderMessage(): void
    {
        $data = [
            'id' => 1,
            'message' => 'Thanks for the purchase!',
            'username' => 'seller',
            'avatar_url' => null,
            'created' => '2024-01-01T12:00:00',
        ];

        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->once())
            ->method('toArray')
            ->willReturn($data);
        $response->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(201);

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with('POST', 'https://api.discogs.com/marketplace/orders/order123/messages', [
                'headers' => ['User-Agent' => 'test-agent'],
                'json' => [
                    'message' => 'Thanks!',
                ]
            ])
            ->willReturn($response);

        $service = new MarketplaceService($this->requestHandler);

        $message = $service->addOrderMessage('order123', 'Thanks!');

        $this->assertInstanceOf(OrderMessage::class, $message);
        $this->assertSame('Thanks for the purchase!', $message->message);
    }
}
