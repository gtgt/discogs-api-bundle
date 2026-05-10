<?php

declare(strict_types = 1);

namespace DiscogsApiBundle\Model\Marketplace;

use DiscogsApiBundle\Model\AbstractModel;

class Order extends AbstractModel
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_PAID = 'paid';

    public const STATUS_SHIPPED = 'shipped';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_REFUNDED = 'refunded';

    public function __construct(
        public readonly int $id,
        public readonly string $status,
        public readonly ?string $buyer,
        public readonly ?string $seller,
        public readonly ?float $total,
        public readonly ?string $currency,
        public readonly ?\DateTimeImmutable $created,
        public readonly ?\DateTimeImmutable $lastActivity,
        public readonly array $items = [],
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) $data['id'],
            status: (string) $data['status'],
            buyer: self::getStringOrNull($data, 'buyer'),
            seller: self::getStringOrNull($data, 'seller'),
            total: isset($data['total']) ? (float) $data['total'] : null,
            currency: self::getStringOrNull($data, 'currency'),
            created: self::getDateTimeImmutableOrNull($data, 'created'),
            lastActivity: self::getDateTimeImmutableOrNull($data, 'last_activity'),
            items: $data['items'] ?? [],
        );
    }
}
