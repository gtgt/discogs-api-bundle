<?php

declare(strict_types = 1);

namespace DiscogsApiBundle\Model;

class User extends AbstractModel
{
    public function __construct(
        public readonly int $id,
        public readonly string $username,
        public readonly ?string $name,
        public readonly ?string $avatarUrl,
        public readonly ?string $resourceUrl,
        public readonly ?string $profile,
        public readonly ?string $location,
        public readonly ?string $website,
        public readonly ?string $joinDate,
        public readonly ?array $wantlist = null,
        public readonly ?array $collection = null,
        public readonly array $folderIds = [],
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) $data['id'],
            username: (string) $data['username'],
            name: self::getStringOrNull($data, 'name'),
            avatarUrl: self::getStringOrNull($data, 'avatar_url'),
            resourceUrl: self::getStringOrNull($data, 'resource_url'),
            profile: self::getStringOrNull($data, 'profile'),
            location: self::getStringOrNull($data, 'location'),
            website: self::getStringOrNull($data, 'website'),
            joinDate: self::getStringOrNull($data, 'join_date'),
            wantlist: $data['wantlist'] ?? null,
            collection: $data['collection'] ?? null,
            folderIds: $data['folder_ids'] ?? [],
        );
    }
}
