<?php

declare(strict_types=1);

namespace DiscogsApiBundle\Model\Release;

use DiscogsApiBundle\Model\AbstractModel;

class ReleaseLabel extends AbstractModel {
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly ?string $catno = null,
        public readonly ?string $entityType = null,
        public readonly ?string $entityTypeName = null,
        public readonly ?string $resourceUrl = null,
        public readonly ?string $thumbnailUrl = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: (int)($data['id'] ?? 0),
            name: (string)$data['name'],
            catno: self::getStringOrNull($data, 'catno'),
            entityType: self::getStringOrNull($data, 'entity_type'),
            entityTypeName: self::getStringOrNull($data, 'entity_type_name'),
            resourceUrl: self::getStringOrNull($data, 'resource_url'),
            thumbnailUrl: self::getStringOrNull($data, 'thumbnail_url'),
        );
    }
}
