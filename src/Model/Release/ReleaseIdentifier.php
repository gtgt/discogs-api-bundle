<?php

declare(strict_types=1);

namespace DiscogsApiBundle\Model\Release;

use DiscogsApiBundle\Model\AbstractModel;

class ReleaseIdentifier extends AbstractModel {
    public function __construct(
        public readonly string $type,
        public readonly string $value,
        public readonly ?string $description = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            type: (string)$data['type'],
            value: (string)$data['value'],
            description: self::getStringOrNull($data, 'description'),
        );
    }
}
