<?php

declare(strict_types = 1);

namespace DiscogsApiBundle\Model\Collection;

use DiscogsApiBundle\Model\AbstractModel;

class CollectionItem extends AbstractModel
{
    public function __construct(
        public readonly int $id,
        public readonly ?string $title,
        public readonly ?string $coverImage,
        public readonly ?array $artists,
        public readonly ?int $rating,
        public readonly ?array $fields,
        public readonly ?string $dateAdded,
        public readonly ?string $resourceUrl,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) $data['id'],
            title: self::getStringOrNull($data['basic_information'] ?? [], 'title'),
            coverImage: self::getStringOrNull($data['basic_information'] ?? [], 'cover_image'),
            artists: $data['basic_information']['artists'] ?? null,
            rating: isset($data['rating']) ? (int) $data['rating'] : null,
            fields: self::getArrayOrNull($data, 'notes'),
            dateAdded: self::getStringOrNull($data, 'date_added'),
            resourceUrl: self::getStringOrNull($data, 'resource_url'),
        );
    }
}
