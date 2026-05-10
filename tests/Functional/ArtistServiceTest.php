<?php

namespace Tamash\DiscogsApiBundle\Tests\Functional;

use Tamash\DiscogsApiBundle\Tests\Unit\UnitTestCase;
use Tamash\DiscogsApiBundle\Client\DiscogsClient;
use Tamash\DiscogsApiBundle\Service\ArtistService;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response;
use Tamash\DiscogsApiBundle\Model\Artist;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ArtistService::class)]
class ArtistServiceTest extends UnitTestCase
{
    private function createMockClient(array $responseData, int $statusCode = 200): MockHttpClient
    {
        $mockResponse = new Response($responseData, $statusCode, [], null, true);
        return new MockHttpClient(fn () => $mockResponse);
    }

    public function testGetArtist(): void
    {
        $responseData = [
            'id' => 123,
            'name' => 'Test Artist',
            'realname' => 'Real Name',
            'profileviews' => 100,
        ];

        $client = $this->createMockClient($responseData);
        $service = new ArtistService($client);

        $artist = $service->getArtist(123);

        $this->assertInstanceOf(Artist::class, $artist);
        $this->assertSame(123, $artist->id);
        $this->assertSame('Test Artist', $artist->name);
    }
}
