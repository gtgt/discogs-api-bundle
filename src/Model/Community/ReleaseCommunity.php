<?php

declare(strict_types=1);

namespace Tamash\DiscogsApiBundle\Model\Community;

use Tamash\DiscogsApiBundle\Model\AbstractModel;

class ReleaseCommunity extends AbstractModel
{
    public function __construct(
        public readonly string $status,
        public readonly ?string $submitterUsername,
        public readonly ?string $submitterResourceUrl,
        public readonly array $contributors = [],
        public readonly Rating $rating,
        public readonly ?Stats $stats = null,
        public readonly ?string $dataQuality = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            status: (string)($data['status'] ?? 'Unknown'),
            submitterUsername: isset($data['submitter']['username']) ? (string)$data['submitter']['username'] : null,
            submitterResourceUrl: isset($data['submitter']['resource_url']) ? (string)$data['submitter']['resource_url'] : null,
            contributors: isset($data['contributors']) && is_array($data['contributors'])
                ? array_map(fn($c) => Contributor::fromArray($c), $data['contributors'])
                : [],
            rating: isset($data['rating']) ? Rating::fromArray($data['rating']) : new Rating(null, 0, null),
            stats: isset($data['stats']) ? Stats::fromArray($data['stats']) : null,
            dataQuality: self::getStringOrNull($data, 'data_quality'),
        );
    }
}
