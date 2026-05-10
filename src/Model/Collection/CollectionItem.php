<?php

declare(strict_types=1);

namespace Tamash\DiscogsApiBundle\Model\Collection;

use Tamash\DiscogsApiBundle\Model\AbstractModel;

class CollectionItem extends AbstractModel
{
    public function __construct(
        public readonly int $id,
        public readonly ?string $title,
        public readonly ?string $thumb,
        public readonly ?array $artists,
        public readonly ?int $rating,
        public readonly ?string $notes,
        public readonly ?string $dateAdded,
        public readonly ?string $resourceUrl,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: (int)$data['release_id'],
            title: self::getStringOrNull($data['basic_information'] ?? [], 'title'),
            thumb: self::getStringOrNull($data['basic_information'] ?? [], 'thumb'),
            artists: $data['basic_information']['artists'] ?? null,
            rating: isset($data['rating']) ? (int)$data['rating'] : null,
            notes: self::getStringOrNull($data, 'notes'),
            dateAdded: self::getStringOrNull($data, 'date_added'),
            resourceUrl: self::getStringOrNull($data, 'resource_url'),
        );
    }
}
