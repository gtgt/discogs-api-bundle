<?php

namespace DiscogsApiBundle\Tests\Unit\Service;

use DiscogsApiBundle\Tests\Unit\UnitTestCase;
use DiscogsApiBundle\Service\CollectionService;
use DiscogsApiBundle\Model\Collection\{CollectionFolder, CollectionItem};
use DiscogsApiBundle\Client\Request\RequestHandler;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class CollectionServiceTest extends UnitTestCase
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

    public function testGetCollectionFolders(): void
    {
        $data = [
            'folders' => [
                ['id' => 0, 'name' => 'All', 'count' => 100, 'resource_url' => 'https://api.discogs.com/folders/0'],
                ['id' => 1, 'name' => 'Wants', 'count' => 5, 'resource_url' => 'https://api.discogs.com/folders/1'],
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
            ->with('GET', 'https://api.discogs.com/users/testuser/collection/folders', ['headers' => ['User-Agent' => 'test-agent']])
            ->willReturn($response);

        $service = new CollectionService($this->requestHandler);

        $folders = $service->getCollectionFolders('testuser');

        $this->assertCount(2, $folders);
        $this->assertInstanceOf(CollectionFolder::class, $folders[0]);
        $this->assertSame('All', $folders[0]->name);
        $this->assertSame(0, $folders[0]->id);
    }

    public function testGetCollectionFolder(): void
    {
        $data = [
            'id' => 1,
            'name' => 'Wants',
            'count' => 5,
            'resource_url' => 'https://api.discogs.com/folders/1',
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
            ->with('GET', 'https://api.discogs.com/users/testuser/collection/folders/1', ['headers' => ['User-Agent' => 'test-agent']])
            ->willReturn($response);

        $service = new CollectionService($this->requestHandler);

        $folder = $service->getCollectionFolder('testuser', 1);

        $this->assertInstanceOf(CollectionFolder::class, $folder);
        $this->assertSame('Wants', $folder->name);
    }
}
