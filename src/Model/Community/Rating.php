<?php

declare(strict_types = 1);

namespace DiscogsApiBundle\Model\Community;

use DiscogsApiBundle\Model\AbstractModel;

class Rating extends AbstractModel
{
    public function __construct(
        public readonly ?float $average,
        public readonly int $count,
        public readonly ?int $userRating,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            average: isset($data['average']) ? (float) $data['average'] : null,
            count: (int) $data['count'],
            userRating: isset($data['rating']) ? (int) $data['rating'] : null,
        );
    }
}
