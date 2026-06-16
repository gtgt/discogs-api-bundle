<?php

declare(strict_types = 1);

namespace DiscogsApiBundle\Model\Release;

use DiscogsApiBundle\Model\AbstractModel;

class ArtistCredit extends AbstractModel
{
    public function __construct(
        public readonly string $name,
        public readonly ?int $id = null,
        public readonly ?string $anv = null,
        public readonly ?string $join = null,
        public readonly ?string $role = null,
        public readonly ?string $tracks = null,
        public readonly ?string $resourceUrl = null,
        public readonly ?string $thumbnailUrl = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            name: (string) $data['name'],
            id: self::getIntOrNull($data, 'id'),
            anv: self::getStringOrNull($data, 'anv'),
            join: self::getStringOrNull($data, 'join'),
            role: self::getStringOrNull($data, 'role'),
            tracks: self::getStringOrNull($data, 'tracks'),
            resourceUrl: self::getStringOrNull($data, 'resource_url'),
            thumbnailUrl: self::getStringOrNull($data, 'thumbnail_url'),
        );
    }
}
