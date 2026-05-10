<?php

declare(strict_types=1);

namespace DiscogsApiBundle\Tests\Unit\Model;

use DiscogsApiBundle\Model\Release;
use PHPUnit\Framework\TestCase;

class ReleaseTest extends TestCase
{
    public function testReleaseFromArray(): void
    {
        $data = [
            'id' => 67890,
            'title' => 'Test Release',
            'description' => 'A test release',
            'data_quality' => 'Correct',
            'year' => 2024,
            'released' => '2024-01-01',
            'country' => 'US',
            'genres' => ['Electronic'],
            'styles' => ['Techno'],
            'labels' => [
                [
                    'id' => 1,
                    'name' => 'Test Label',
                    'catno' => 'TL-001',
                ]
            ],
            'artists' => [
                [
                    'id' => 123,
                    'name' => 'Test Artist',
                    'anv' => '',
                    'join' => '',
                    'role' => '',
                    'tracks' => '',
                ]
            ],
            'master' => ['id' => 999, 'title' => 'Master Title'],
            'formats' => [['name' => 'Vinyl']],
            'catno' => 'TL-001',
            'thumb' => 'https://example.com/thumb.jpg',
        ];

        $release = Release::fromArray($data);

        $this->assertSame(67890, $release->id);
        $this->assertSame('Test Release', $release->title);
        $this->assertSame(2024, $release->year);
        $this->assertSame('US', $release->country);
        $this->assertContains('Electronic', $release->genres);
        $this->assertCount(1, $release->labels);
        $this->assertSame('Test Label', $release->labels[0]['name']);
        $this->assertNotNull($release->master);
        $this->assertSame(999, $release->master->id);
    }
}
