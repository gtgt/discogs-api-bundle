<?php

declare(strict_types=1);

namespace DiscogsApiBundle\Model;

class FieldData extends AbstractModel {
    public function __construct(
        public readonly int $fieldId,
        public readonly ?string $value = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            fieldId: (int)$data['field_id'],
            value: self::getStringOrNull($data, 'value'),
        );
    }
}
