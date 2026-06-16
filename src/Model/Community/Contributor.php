<?php

declare(strict_types=1);

namespace DiscogsApiBundle\Model\Community;

use DiscogsApiBundle\Model\AbstractModel;

class Contributor extends AbstractModel {
    public function __construct(
        public readonly string $username,
        public readonly ?string $resourceUrl,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            username: (string)$data['username'],
            resourceUrl: self::getStringOrNull($data, 'resource_url'),
        );
    }
}
