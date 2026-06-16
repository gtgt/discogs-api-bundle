<?php

declare(strict_types=1);

namespace DiscogsApiBundle\Model\Release;

use DiscogsApiBundle\Model\AbstractModel;

class ReleaseSeries extends AbstractModel {
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly ?string $catno = null,
        public readonly ?string $number = null,
        public readonly ?string $resourceUrl = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: (int)($data['id'] ?? 0),
            name: (string)$data['name'],
            catno: self::getStringOrNull($data, 'catno'),
            number: self::getStringOrNull($data, 'number'),
            resourceUrl: self::getStringOrNull($data, 'resource_url'),
        );
    }
}
