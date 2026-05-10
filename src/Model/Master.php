<?php

declare(strict_types=1);

namespace Tamash\DiscogsApiBundle\Model;

use Tamash\DiscogsApiBundle\Model\AbstractModel;

class Master extends AbstractModel
{
    public function __construct(
        public readonly int $id,
        public readonly string $title,
        public readonly ?string $description,
        public readonly ?string $dataQuality,
        public readonly ?int $year,
        public readonly ?string $released,
        public readonly array $genres = [],
        public readonly array $styles = [],
        public readonly array $artists = [],
        public readonly array $versions = [],
        public readonly ?string $thumb = null,
        public readonly ?string $coverImage = null,
        public readonly ?string $resourceUrl = null,
        public readonly ?array $mainRelease = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: (int)$data['id'],
            title: (string)$data['title'],
            description: self::getStringOrNull($data, 'description'),
            dataQuality: self::getStringOrNull($data, 'data_quality'),
            year: self::getIntOrNull($data, 'year'),
            released: self::getStringOrNull($data, 'released'),
            genres: $data['genres'] ?? [],
            styles: $data['styles'] ?? [],
            artists: $data['artists'] ?? [],
            versions: $data['versions'] ?? [],
            thumb: self::getStringOrNull($data, 'thumb'),
            coverImage: self::getStringOrNull($data, 'cover_image'),
            resourceUrl: self::getStringOrNull($data, 'resource_url'),
            mainRelease: $data['main_release'] ?? null,
        );
    }
}
