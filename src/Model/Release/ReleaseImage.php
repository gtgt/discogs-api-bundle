<?php

declare(strict_types=1);

namespace DiscogsApiBundle\Model\Release;

use DiscogsApiBundle\Model\AbstractModel;

class ReleaseImage extends AbstractModel {
    public function __construct(
        public readonly string $type,
        public readonly string $uri,
        public readonly ?string $resourceUrl = null,
        public readonly ?string $uri150 = null,
        public readonly ?int $width = null,
        public readonly ?int $height = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            type: (string)$data['type'],
            uri: (string)$data['uri'],
            resourceUrl: self::getStringOrNull($data, 'resource_url'),
            uri150: self::getStringOrNull($data, 'uri150'),
            width: self::getIntOrNull($data, 'width'),
            height: self::getIntOrNull($data, 'height'),
        );
    }
}
