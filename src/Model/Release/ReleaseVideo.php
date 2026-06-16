<?php

declare(strict_types = 1);

namespace DiscogsApiBundle\Model\Release;

use DiscogsApiBundle\Model\AbstractModel;

class ReleaseVideo extends AbstractModel
{
    public function __construct(
        public readonly string $uri,
        public readonly ?string $title = null,
        public readonly ?string $description = null,
        public readonly ?int $duration = null,
        public readonly ?bool $embed = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            uri: (string) $data['uri'],
            title: self::getStringOrNull($data, 'title'),
            description: self::getStringOrNull($data, 'description'),
            duration: self::getIntOrNull($data, 'duration'),
            embed: self::getBoolOrNull($data, 'embed'),
        );
    }
}
