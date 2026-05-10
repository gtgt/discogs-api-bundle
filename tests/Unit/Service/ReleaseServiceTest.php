<?php

namespace Tamash\DiscogsApiBundle\Tests\Unit\Service;

use Tamash\DiscogsApiBundle\Tests\Unit\UnitTestCase;
use Tamash\DiscogsApiBundle\Service\ReleaseService;
use Tamash\DiscogsApiBundle\Model\Release;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response;

class ReleaseServiceTest extends UnitTestCase
{
    private function createMockHttpClient(array $responseData, int $status = 200): MockHttpClient
    {
        return new MockHttpClient(fn () => new Response($responseData, $status));
    }

    public function testGetRelease(): void
    {
        $data = [
            'id' => 12345,
            'title' => 'Test Album',
            'year' => 2024,
            'country' => 'US',
            'genres' => ['Rock'],
            'labels' => [['name' => 'Test Label']],
        ];

        $client = $this->createMockHttpClient($data);
        $service = new ReleaseService($client);

        $release = $service->getRelease(12345);

        $this->assertInstanceOf(Release::class, $release);
        $this->assertSame(12345, $release->id);
        $this->assertSame('Test Album', $release->title);
    }

    public function testGetReleaseWithCurrency(): void
    {
        $data = [
            'id' => 12345,
            'title' => 'Test Album',
        ];

        $client = $this->createMockHttpClient($data);
        $service = new ReleaseService($client);

        $release = $service->getRelease(12345, 'USD');

        // Response should include curr_abbr parameter
        $this->assertInstanceOf(Release::class, $release);
    }
}
