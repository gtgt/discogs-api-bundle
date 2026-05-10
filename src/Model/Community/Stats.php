<?php

declare(strict_types=1);

namespace Tamash\DiscogsApiBundle\Model\Community;

use Tamash\DiscogsApiBundle\Model\AbstractModel;

class Stats extends AbstractModel
{
    public function __construct(
        public readonly ?int $inCollection,
        public readonly ?int $inWantlist,
        public readonly ?int $haves,
        public readonly ?int $wants,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            inCollection: isset($data['in_collection']) ? (int)$data['in_collection'] : null,
            inWantlist: isset($data['in_wantlist']) ? (int)$data['in_wantlist'] : null,
            haves: isset($data['haves']) ? (int)$data['haves'] : null,
            wants: isset($data['wants']) ? (int)$data['wants'] : null,
        );
    }
}
