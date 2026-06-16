<?php

declare(strict_types = 1);

namespace DiscogsApiBundle\Model\Release;

use DiscogsApiBundle\Model\AbstractModel;

class ReleaseFormat extends AbstractModel
{
    /**
     * @param string[] $descriptions
     */
    public function __construct(
        public readonly string $name,
        public readonly ?string $qty = null,
        public readonly array $descriptions = [],
        public readonly ?string $text = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            name: (string) $data['name'],
            qty: self::getStringOrNull($data, 'qty'),
            descriptions: isset($data['descriptions']) && is_array($data['descriptions'])
                ? array_map('strval', $data['descriptions'])
                : [],
            text: self::getStringOrNull($data, 'text'),
        );
    }
}
