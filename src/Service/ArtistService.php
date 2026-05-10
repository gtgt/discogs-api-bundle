<?php

declare(strict_types=1);

namespace Tamash\DiscogsApiBundle\Service;

use Tamash\DiscogsApiBundle\Client\Request\RequestHandler;
use Tamash\DiscogsApiBundle\Model\Artist;
use Tamash\DiscogsApiBundle\Client\Response\PaginatedResponse;

class ArtistService
{
    private RequestHandler $requestHandler;
    private string $baseUrl;

    public function __construct(RequestHandler $requestHandler, string $baseUrl = 'https://api.discogs.com')
    {
        $this->requestHandler = $requestHandler;
        $this->baseUrl = $baseUrl;
    }

    public function getArtist(int $id, array $options = []): Artist
    {
        $url = sprintf('%s/artists/%d', $this->baseUrl, $id);
        $response = $this->requestHandler->get($url, ['query' => $options]);
        $data = $response->toArray(false);

        return Artist::fromArray($data);
    }

    public function getArtistReleases(int $artistId, array $options = []): PaginatedResponse
    {
        $url = sprintf('%s/artists/%d/releases', $this->baseUrl, $artistId);
        $response = $this->requestHandler->get($url, ['query' => $options]);
        $data = $response->toArray(false);

        return \Tamash\DiscogsApiBundle\Client\Request\RequestFactory::createPaginatedResponse($data, $response);
    }

    public function getArtistByName(string $name): ?Artist
    {
        $results = $this->search($name, ['type' => 'artist']);
        foreach ($results as $artist) {
            if (strcasecmp($artist->name, $name) === 0) {
                return $artist;
            }
        }
        return null;
    }

    public function search(string $query, array $options = []): PaginatedResponse
    {
        $url = $this->baseUrl . '/database/search';
        $response = $this->requestHandler->get($url, [
            'query' => array_merge(['q' => $query], $options)
        ]);
        $data = $response->toArray(false);

        return \Tamash\DiscogsApiBundle\Client\Request\RequestFactory::createPaginatedResponse($data, $response);
    }
}
