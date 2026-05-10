<?php
namespace DiscogsApiBundle\Tests\Integration;

use DiscogsApiBundle\Tests\Integration\IntegrationTestCase;
use DiscogsApiBundle\Client\DiscogsClient;
use PHPUnit\Framework\Attributes\Group;

#[Group('live')]
class LiveApiTest extends IntegrationTestCase
{
    private DiscogsClient $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::getContainer()->get(DiscogsClient::class);
    }

    public function testGetRelease(): void
    {
        // Test with a known release (Daft Punk - Homework)
        $release = $this->client->getRelease(249504);

        $this->assertNotNull($release);
        $this->assertSame(249504, $release->id);
        $this->assertNotEmpty($release->title);
    }

    public function testGetArtist(): void
    {
        $artist = $this->client->getArtist(1);

        $this->assertNotNull($artist);
        $this->assertSame(1, $artist->id);
        $this->assertNotEmpty($artist->name);
    }

    public function testSearch(): void
    {
        $results = $this->client->search('Daft Punk', [
            'type' => 'artist',
            'per_page' => 5,
        ]);

        $this->assertGreaterThan(0, count($results));
        $this->assertLessThanOrEqual(5, count($results));
    }
}
