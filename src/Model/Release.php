<?php

declare(strict_types = 1);

namespace DiscogsApiBundle\Model;

use DiscogsApiBundle\Model\Community\ReleaseCommunity;
use DiscogsApiBundle\Model\Community\Stats;

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
        public readonly ?Master $master = null,
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
        public readonly ?string $notes = null,
        public readonly ?ReleaseCommunity $community = null,
        public readonly ?Stats $statistics = null,
        public readonly ?string $resourceUrl = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) $data['id'],
            title: (string) $data['title'],
            description: self::getStringOrNull($data, 'description'),
            dataQuality: self::getStringOrNull($data, 'data_quality'),
            year: self::getIntOrNull($data, 'year'),
            released: self::getStringOrNull($data, 'released'),
            country: self::getStringOrNull($data, 'country'),
            genres: $data['genres'] ?? [],
            styles: $data['styles'] ?? [],
            labels: $data['labels'] ?? [],
            artists: $data['artists'] ?? [],
            master: isset($data['master']) ? Master::fromArray($data['master']) : null,
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
            community: isset($data['community']) ? ReleaseCommunity::fromArray($data['community']) : null,
            statistics: isset($data['stats']) ? Stats::fromArray($data['stats']) : null,
            resourceUrl: self::getStringOrNull($data, 'resource_url'),
        );
    }
}
