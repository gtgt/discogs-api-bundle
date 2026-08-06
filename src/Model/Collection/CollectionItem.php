<?php

declare(strict_types=1);

namespace DiscogsApiBundle\Model\Collection;

use DiscogsApiBundle\Model\{AbstractModel, Artist, FieldData, Format};

class CollectionItem extends AbstractModel {
    /**
     * @param Format[] $formats
     * @param Artist[] $artists
     * @param FieldData[] $fields
     */
    public function __construct(
        public readonly int $id,
        public readonly ?string $title,
        public readonly ?int $folderId,
        public readonly array $formats,
        public readonly ?string $coverImage,
        public readonly array $artists,
        public readonly ?float $rating,
        public readonly array $fields,
        public readonly ?string $dateAdded,
        public readonly ?string $resourceUrl,
    ) {}

    public static function fromArray(array $data): self
    {
        $releaseId = (int)($data['basic_information']['id'] ?? $data['release_id'] ?? $data['id']);

        return new self(
            id: $releaseId,
            title: self::getStringOrNull($data['basic_information'] ?? [], 'title'),
            folderId: self::getIntOrNull($data, 'folder_id'),
            formats: self::mapModels($data['basic_information']['formats'] ?? [], Format::class),
            coverImage: self::getStringOrNull($data['basic_information'] ?? [], 'cover_image'),
            artists: self::mapModels($data['basic_information']['artists'] ?? [], Artist::class),
            rating: isset($data['rating']) ? (float)$data['rating'] : null,
            fields: self::mapModels($data['notes'] ?? [], FieldData::class),
            dateAdded: self::getStringOrNull($data, 'date_added'),
            resourceUrl: self::getStringOrNull($data, 'resource_url'),
        );
    }

    public function getFieldValue(int $fieldId): ?string
    {
        return array_find($this->fields, fn(FieldData $field) => $field->fieldId === $fieldId)?->value;
    }
}
