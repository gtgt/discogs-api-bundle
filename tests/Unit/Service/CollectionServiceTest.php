<?php

namespace Tamash\DiscogsApiBundle\Tests\Unit\Service;

use Tamash\DiscogsApiBundle\Tests\Unit\UnitTestCase;
use Tamash\DiscogsApiBundle\Service\CollectionService;
use Tamash\DiscogsApiBundle\Model\Collection\{CollectionFolder, CollectionItem};
use Tamash\DiscogsApiBundle\Client\Response\PaginatedResponse;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response;

class CollectionServiceTest extends UnitTestCase
{
    public function testGetCollectionFolders(): void
    {
        $data = [
            'folders' => [
                ['id' => 0, 'name' => 'All', 'count' => 100, 'resource_url' => 'https://api.discogs.com/folders/0'],
                ['id' => 1, 'name' => 'Wants', 'count' => 5, 'resource_url' => 'https://api.discogs.com/folders/1'],
            ],
        ];

        $client = new MockHttpClient(fn () => new Response($data, 200));
        $service = new CollectionService($client);

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

        $client = new MockHttpClient(fn () => new Response($data, 200));
        $service = new CollectionService($client);

        $folder = $service->getCollectionFolder('testuser', 1);

        $this->assertInstanceOf(CollectionFolder::class, $folder);
        $this->assertSame('Wants', $folder->name);
    }
}
