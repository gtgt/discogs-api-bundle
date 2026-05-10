<?php

declare(strict_types=1);

namespace Tamash\DiscogsApiBundle\Tests\Unit\Model;

use Tamash\DiscogsApiBundle\Model\Artist;
use PHPUnit\Framework\TestCase;

class ArtistTest extends TestCase
{
    public function testArtistFromArray(): void
    {
        $data = [
            'id' => 12345,
            'name' => 'Test Artist',
            'realname' => 'Real Name',
            'profile' => 'Test profile',
            'profileviews' => 100,
            'thumb' => 'https://example.com/thumb.jpg',
            'resource_url' => 'https://api.discogs.com/artists/12345',
            'uri' => 'https://www.discogs.com/artist/12345',
            'aliases' => [['id' => 1, 'name' => 'Alias']],
            'members' => [['id' => 2, 'name' => 'Member']],
            'urls' => ['http://example.com'],
            'namevariations' => ['Variation'],
            'images' => [],
            'data_quality' => 'Correct',
        ];

        $artist = Artist::fromArray($data);

        $this->assertSame(12345, $artist->id);
        $this->assertSame('Test Artist', $artist->name);
        $this->assertSame('Real Name', $artist->realname);
        $this->assertSame(100, $artist->profileViews);
        $this->assertCount(1, $artist->aliases);
    }
}
