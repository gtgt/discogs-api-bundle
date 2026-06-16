<?php

declare(strict_types=1);

namespace DiscogsApiBundle\Model;

class Artist extends AbstractModel {
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly ?string $realname,
        public readonly ?string $profile,
        public readonly ?int $profileViews,
        public readonly ?string $thumb,
        public readonly ?string $resourceUrl,
        public readonly ?string $uri,
        public readonly array $aliases = [],
        public readonly array $members = [],
        public readonly array $urls = [],
        public readonly array $nameVariations = [],
        public readonly ?array $images = null,
        public readonly ?string $dataQuality = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: (int)$data['id'],
            name: (string)$data['name'],
            realname: self::getStringOrNull($data, 'realname'),
            profile: self::getStringOrNull($data, 'profile'),
            profileViews: self::getIntOrNull($data, 'profileviews'),
            thumb: self::getStringOrNull($data, 'thumb'),
            resourceUrl: self::getStringOrNull($data, 'resource_url'),
            uri: self::getStringOrNull($data, 'uri'),
            aliases: $data['aliases'] ?? [],
            members: $data['members'] ?? [],
            urls: $data['urls'] ?? [],
            nameVariations: $data['namevariations'] ?? [],
            images: $data['images'] ?? null,
            dataQuality: $data['data_quality'] ?? null,
        );
    }
}
