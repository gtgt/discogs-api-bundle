<?php

declare(strict_types = 1);

namespace DiscogsApiBundle\Model;

use DiscogsApiBundle\Model\Community\ReleaseCommunity;
use DiscogsApiBundle\Model\Community\Stats;
use DiscogsApiBundle\Model\Release\ReleaseCompany;
use DiscogsApiBundle\Model\Release\ReleaseFormat;
use DiscogsApiBundle\Model\Release\ReleaseIdentifier;
use DiscogsApiBundle\Model\Release\ReleaseImage;
use DiscogsApiBundle\Model\Release\ReleaseLabel;
use DiscogsApiBundle\Model\Release\ReleaseSeries;
use DiscogsApiBundle\Model\Release\ReleaseVideo;
use DiscogsApiBundle\Model\Release\Track;

class Release extends AbstractModel
{
    /**
     * @param ReleaseLabel[]      $labels
     * @param ReleaseSeries[]     $series
     * @param ReleaseFormat[]     $formats
     * @param ReleaseImage[]      $images
     * @param ReleaseVideo[]|null $videos
     * @param ReleaseCompany[]|null $companies
     * @param ReleaseIdentifier[]|null $identifiers
     * @param Track[]|null        $tracklist
     */
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
        public readonly array $series = [],
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
            labels: self::mapModels($data['labels'] ?? [], ReleaseLabel::class),
            series: self::mapModels($data['series'] ?? [], ReleaseSeries::class),
            artists: $data['artists'] ?? [],
            master: isset($data['master']) ? Master::fromArray($data['master']) : null,
            mainReleaseId: $data['main_release'] ?? null,
            formats: self::mapModels($data['formats'] ?? [], ReleaseFormat::class),
            formatQuantity: self::getIntOrNull($data, 'format_quantity'),
            catno: self::getStringOrNull($data, 'catno'),
            barcode: self::getStringOrNull($data, 'barcode'),
            thumb: self::getStringOrNull($data, 'thumb'),
            coverImage: self::getStringOrNull($data, 'cover_image'),
            images: self::mapModels($data['images'] ?? [], ReleaseImage::class),
            videos: self::mapModelsOrNull($data['videos'] ?? null, ReleaseVideo::class),
            companies: self::mapModelsOrNull($data['companies'] ?? null, ReleaseCompany::class),
            identifiers: self::mapModelsOrNull($data['identifiers'] ?? null, ReleaseIdentifier::class),
            tracklist: self::mapModelsOrNull($data['tracklist'] ?? null, Track::class),
            extraArtists: $data['extraartists'] ?? null,
            notes: $data['notes'] ?? null,
            community: isset($data['community']) ? ReleaseCommunity::fromArray($data['community']) : null,
            statistics: isset($data['stats']) ? Stats::fromArray($data['stats']) : null,
            resourceUrl: self::getStringOrNull($data, 'resource_url'),
        );
    }

    /**
     * @template T of AbstractModel
     *
     * @param class-string<T> $modelClass
     *
     * @return T[]
     */
    private static function mapModels(array $items, string $modelClass): array
    {
        return array_map(
            static fn (array $item) => $modelClass::fromArray($item),
            $items,
        );
    }

    /**
     * @template T of AbstractModel
     *
     * @param class-string<T> $modelClass
     *
     * @return T[]|null
     */
    private static function mapModelsOrNull(?array $items, string $modelClass): ?array
    {
        if ($items === null) {
            return null;
        }

        return self::mapModels($items, $modelClass);
    }
}
