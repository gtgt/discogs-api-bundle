<?php

declare(strict_types=1);

namespace DiscogsApiBundle\Model;

class Format extends AbstractModel {
    /**
     * @param string[] $descriptions
     */
    public function __construct(
        public readonly string $name,
        public readonly ?int $qty = null,
        public readonly array $descriptions = [],
        public readonly ?string $text = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: (string)$data['name'],
            qty: self::getIntOrNull($data, 'qty'),
            descriptions: isset($data['descriptions']) && is_array($data['descriptions'])
                ? array_map('strval', $data['descriptions'])
                : [],
            text: self::getStringOrNull($data, 'text'),
        );
    }
}
