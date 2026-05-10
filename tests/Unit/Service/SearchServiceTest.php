<?php

namespace Tamash\DiscogsApiBundle\Tests\Unit\Service;

use Tamash\DiscogsApiBundle\Tests\Unit\UnitTestCase;
use Tamash\DiscogsApiBundle\Service\SearchService;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response;
use Tamash\DiscogsApiBundle\Client\Response\PaginatedResponse;

class SearchServiceTest extends UnitTestCase
{
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

        $client = new MockHttpClient(fn () => new Response($data, 200));
        $service = new SearchService($client);

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

        $client = new MockHttpClient(fn () => new Response($data, 200));
        $service = new SearchService($client);

        $results = $service->searchReleases('Homework');

        $this->assertCount(1, $results);
        $this->assertSame('release', $results[0]['type']);
    }
}
