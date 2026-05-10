<?php

declare(strict_types = 1);

namespace DiscogsApiBundle\Model\Marketplace;

use DiscogsApiBundle\Model\AbstractModel;

class Listing extends AbstractModel
{
    public function __construct(
        public readonly int $id,
        public readonly ?array $releaseInfo,
        public readonly string $status,
        public readonly float $price,
        public readonly string $currency,
        public readonly string $condition,
        public readonly ?string $sleeveCondition,
        public readonly ?string $comments,
        public readonly bool $allowOffers,
        public readonly ?string $seller,
        public readonly ?string $location,
        public readonly ?float $weight,
        public readonly ?int $formatQuantity,
        public readonly ?string $externalId,
        public readonly ?string $posted,
        public readonly ?string $expires,
        public readonly ?int $shipsWithin,
        public readonly array $images = [],
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) $data['id'],
            releaseInfo: $data['release'] ?? null,
            status: (string) $data['status'],
            price: (float) $data['price'],
            currency: (string) $data['currency'],
            condition: (string) $data['condition'],
            sleeveCondition: self::getStringOrNull($data, 'sleeve_condition'),
            comments: self::getStringOrNull($data, 'comments'),
            allowOffers: (bool) ($data['allow_offers'] ?? false),
            seller: self::getStringOrNull($data, 'seller'),
            location: self::getStringOrNull($data, 'location'),
            weight: isset($data['weight']) ? (float) $data['weight'] : null,
            formatQuantity: isset($data['format_quantity']) ? (int) $data['format_quantity'] : null,
            externalId: self::getStringOrNull($data, 'external_id'),
            posted: self::getStringOrNull($data, 'posted'),
            expires: self::getStringOrNull($data, 'expires'),
            shipsWithin: isset($data['ships_within']) ? (int) $data['ships_within'] : null,
            images: $data['images'] ?? [],
        );
    }
}
