<?php

declare(strict_types = 1);

namespace DiscogsApiBundle\Model\Release;

use DiscogsApiBundle\Model\AbstractModel;

class Track extends AbstractModel
{
    /**
     * @param ArtistCredit[] $artists
     * @param ArtistCredit[] $extraArtists
     * @param Track[]        $subTracks
     */
    public function __construct(
        public readonly ?string $position = null,
        public readonly ?string $type = null,
        public readonly ?string $title = null,
        public readonly ?string $duration = null,
        public readonly array $artists = [],
        public readonly array $extraArtists = [],
        public readonly array $subTracks = [],
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            position: self::getStringOrNull($data, 'position'),
            type: self::getStringOrNull($data, 'type_') ?? self::getStringOrNull($data, 'type'),
            title: self::getStringOrNull($data, 'title'),
            duration: self::getStringOrNull($data, 'duration'),
            artists: isset($data['artists']) && is_array($data['artists'])
                ? array_map(fn (array $artist): ArtistCredit => ArtistCredit::fromArray($artist), $data['artists'])
                : [],
            extraArtists: isset($data['extraartists']) && is_array($data['extraartists'])
                ? array_map(fn (array $artist): ArtistCredit => ArtistCredit::fromArray($artist), $data['extraartists'])
                : [],
            subTracks: isset($data['sub_tracks']) && is_array($data['sub_tracks'])
                ? array_map(fn (array $track): self => self::fromArray($track), $data['sub_tracks'])
                : [],
        );
    }
}
