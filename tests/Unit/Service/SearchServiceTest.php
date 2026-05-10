<?php

namespace DiscogsApiBundle\Tests\Unit\Service;

use DiscogsApiBundle\Tests\Unit\UnitTestCase;
use DiscogsApiBundle\Service\SearchService;
use DiscogsApiBundle\Client\Request\RequestHandler;
use DiscogsApiBundle\Client\Response\PaginatedResponse;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class SearchServiceTest extends UnitTestCase
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

    public function testSearchArtists(): void
    {
        $data = [
            'pagination' => [
                'page' => 1,
                'pages' => 2,
                'per_page' => 5,
                'urls' => [],
            ],
            'results' => [
                [
                    'type' => 'artist',
                    'id' => 123,
                    'title' => 'Daft Punk',
                    'thumb' => 'https://example.com/thumb.jpg',
                ],
            ],
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
            ->with('GET', 'https://api.discogs.com/database/search', [
                'headers' => ['User-Agent' => 'test-agent'],
                'query' => [
                    'q' => 'Daft Punk',
                    'per_page' => 5,
                    'type' => 'artist',
                ]
            ])
            ->willReturn($response);

        $service = new SearchService($this->requestHandler);

        $results = $service->searchArtists('Daft Punk', ['per_page' => 5]);

        $this->assertInstanceOf(PaginatedResponse::class, $results);
        $this->assertSame(1, $results->getPage());
        $this->assertSame(2, $results->getPages());
        $item = $results->getItems()[0];
        $this->assertSame('artist', $item['type']);
        $this->assertSame(123, $item['id']);
    }

    public function testSearchReleases(): void
    {
        $data = [
            'pagination' => [
                'page' => 1,
                'pages' => 1,
                'per_page' => 1,
                'urls' => [],
            ],
            'results' => [
                [
                    'type' => 'release',
                    'id' => 456,
                    'title' => 'Homework',
                ],
            ],
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
            ->with('GET', 'https://api.discogs.com/database/search', [
                'headers' => ['User-Agent' => 'test-agent'],
                'query' => [
                    'q' => 'Homework',
                    'type' => 'release',
                ]
            ])
            ->willReturn($response);

        $service = new SearchService($this->requestHandler);

        $results = $service->searchReleases('Homework');

        $this->assertCount(1, $results);
        $this->assertSame('release', $results->getItems()[0]['type']);
        $this->assertSame(456, $results->getItems()[0]['id']);
    }
}
