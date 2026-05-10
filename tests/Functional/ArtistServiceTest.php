<?php

namespace DiscogsApiBundle\Tests\Functional;

use DiscogsApiBundle\Tests\Unit\UnitTestCase;
use DiscogsApiBundle\Client\DiscogsClient;
use DiscogsApiBundle\Service\ArtistService;
use DiscogsApiBundle\Model\Artist;
use DiscogsApiBundle\Client\Request\RequestHandler;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class ArtistServiceTest extends UnitTestCase
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

    public function testGetArtist(): void
    {
        $responseData = [
            'id' => 123,
            'name' => 'Test Artist',
            'realname' => 'Real Name',
            'profileviews' => 100,
        ];

        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->once())
            ->method('toArray')
            ->willReturn($responseData);
        $response->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(200);

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with('GET', 'https://api.discogs.com/artists/123', [
                'headers' => ['User-Agent' => 'test-agent'],
                'query' => []
            ])
            ->willReturn($response);

        $service = new ArtistService($this->requestHandler);

        $artist = $service->getArtist(123);

        $this->assertInstanceOf(Artist::class, $artist);
        $this->assertSame(123, $artist->id);
        $this->assertSame('Test Artist', $artist->name);
    }
}
