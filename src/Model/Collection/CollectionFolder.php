<?php

declare(strict_types=1);

namespace Tamash\DiscogsApiBundle\Model\Collection;

use Tamash\DiscogsApiBundle\Model\AbstractModel;

class CollectionFolder extends AbstractModel
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly ?int $count,
        public readonly ?string $resourceUrl,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: (int)$data['id'],
            name: (string)$data['name'],
            count: self::getIntOrNull($data, 'count'),
            resourceUrl: self::getStringOrNull($data, 'resource_url'),
        );
    }
}
