<?php

declare(strict_types=1);

namespace DiscogsApiBundle\Model;

abstract class AbstractModel {
    protected static function getStringOrNull(array $data, string $key): ?string
    {
        return isset($data[$key]) && $data[$key] !== null ? (string)$data[$key] : null;
    }

    protected static function getIntOrNull(array $data, string $key): ?int
    {
        return isset($data[$key]) && is_numeric($data[$key]) ? (int)$data[$key] : null;
    }

    protected static function getFloatOrNull(array $data, string $key): ?float
    {
        return isset($data[$key]) && is_numeric($data[$key]) ? (float)$data[$key] : null;
    }

    protected static function getBoolOrNull(array $data, string $key): ?bool
    {
        return isset($data[$key]) ? (bool)$data[$key] : null;
    }

    protected static function getDateTimeImmutableOrNull(array $data, string $key): ?\DateTimeImmutable
    {
        if (!isset($data[$key]) || !is_string($data[$key])) {
            return null;
        }
        try {
            return new \DateTimeImmutable($data[$key]);
        } catch (\Throwable) {
            return null;
        }
    }

    protected static function getArrayOrNull(array $data, string $key): ?array
    {
        return isset($data[$key]) && is_array($data[$key]) ? $data[$key] : null;
    }

    /**
     * @template T of AbstractModel
     *
     * @param class-string<T> $modelClass
     *
     * @return T[]|null
     */
    protected static function mapModelsOrNull(?array $items, string $modelClass): ?array
    {
        if ($items === null) {
            return null;
        }
        return self::mapModels($items, $modelClass);
    }

    /**
     * @template T of AbstractModel
     *
     * @param class-string<T> $modelClass
     *
     * @return T[]
     */
    protected static function mapModels(array $items, string $modelClass): array
    {
        return array_map(
            static fn(array $item) => $modelClass::fromArray($item),
            $items,
        );
    }

    abstract public static function fromArray(array $data): self;
}
