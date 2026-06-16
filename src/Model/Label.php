<?php

declare(strict_types=1);

namespace DiscogsApiBundle\Model;

class Label extends AbstractModel {
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly ?string $contactInfo,
        public readonly ?string $profile,
        public readonly ?int $profileViews,
        public readonly ?string $thumb,
        public readonly ?string $resourceUrl,
        public readonly ?string $uri,
        public readonly array $sublabels = [],
        public readonly array $urls = [],
        public readonly ?array $images = null,
        public readonly ?array $dataQuality = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: (int)$data['id'],
            name: (string)$data['name'],
            contactInfo: self::getStringOrNull($data, 'contact_info'),
            profile: self::getStringOrNull($data, 'profile'),
            profileViews: self::getIntOrNull($data, 'profileviews'),
            thumb: self::getStringOrNull($data, 'thumb'),
            resourceUrl: self::getStringOrNull($data, 'resource_url'),
            uri: self::getStringOrNull($data, 'uri'),
            sublabels: $data['sublabels'] ?? [],
            urls: $data['urls'] ?? [],
            images: $data['images'] ?? null,
            dataQuality: $data['data_quality'] ?? null,
        );
    }
}
