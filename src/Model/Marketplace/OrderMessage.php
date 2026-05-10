<?php

declare(strict_types=1);

namespace Tamash\DiscogsApiBundle\Model\Marketplace;

use Tamash\DiscogsApiBundle\Model\AbstractModel;

class OrderMessage extends AbstractModel
{
    public function __construct(
        public readonly int $id,
        public readonly string $message,
        public readonly string $username,
        public readonly ?string $avatarUrl,
        public readonly \DateTimeImmutable $created,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: (int)$data['id'],
            message: (string)$data['message'],
            username: (string)$data['username'],
            avatarUrl: self::getStringOrNull($data, 'avatar_url'),
            created: new \DateTimeImmutable($data['created']),
        );
    }
}
