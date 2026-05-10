<?php

declare(strict_types=1);

namespace Tamash\DiscogsApiBundle\Model;

use Tamash\DiscogsApiBundle\Model\AbstractModel;

class Release extends AbstractModel
{
    public function __construct(
        public readonly int $id,
        public readonly string $title,
        public readonly ?string $description,
        public readonly ?string $dataQuality,
        public readonly ?int $year,
        public readonly ?string $released,
        public readonly ?string $country,
        public readonly array $genres = [],
        public readonly array $styles = [],
        public readonly array $labels = [],
        public readonly array $artists = [],
        public readonly ?array $master = null,
        public readonly ?string $mainReleaseId = null,
        public readonly array $formats = [],
        public readonly ?int $formatQuantity = null,
        public readonly ?string $catno = null,
        public readonly ?string $barcode = null,
        public readonly ?string $thumb = null,
        public readonly ?string $coverImage = null,
        public readonly array $images = [],
        public readonly ?array $videos = null,
        public readonly ?array $companies = null,
        public readonly ?array $identifiers = null,
        public readonly ?array $tracklist = null,
        public readonly ?array $extraArtists = null,
        public readonly ?array $notes = null,
        public readonly ?array $releaseData = null,
        public readonly ?array $community = null,
        public readonly ?array $statistics = null,
        public readonly ?string $resourceUrl = null,
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
            country: self::getStringOrNull($data, 'country'),
            genres: $data['genres'] ?? [],
            styles: $data['styles'] ?? [],
            labels: $data['labels'] ?? [],
            artists: $data['artists'] ?? [],
            master: $data['master'] ?? null,
            mainReleaseId: $data['main_release'] ?? null,
            formats: $data['formats'] ?? [],
            formatQuantity: self::getIntOrNull($data, 'format_quantity'),
            catno: self::getStringOrNull($data, 'catno'),
            barcode: self::getStringOrNull($data, 'barcode'),
            thumb: self::getStringOrNull($data, 'thumb'),
            coverImage: self::getStringOrNull($data, 'cover_image'),
            images: $data['images'] ?? [],
            videos: $data['videos'] ?? null,
            companies: $data['companies'] ?? null,
            identifiers: $data['identifiers'] ?? null,
            tracklist: $data['tracklist'] ?? null,
            extraArtists: $data['extraartists'] ?? null,
            notes: $data['notes'] ?? null,
            releaseData: $data['released'] ?? null,
            community: $data['community'] ?? null,
            statistics: $data['stats'] ?? null,
            resourceUrl: self::getStringOrNull($data, 'resource_url'),
        );
    }
}
