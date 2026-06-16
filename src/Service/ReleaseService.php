<?php

declare(strict_types=1);

namespace DiscogsApiBundle\Service;

use DiscogsApiBundle\Client\Request\RequestHandler;
use DiscogsApiBundle\Model\Release;

class ReleaseService {
    private RequestHandler $requestHandler;

    private string $baseUrl;

    public function __construct(RequestHandler $requestHandler, string $baseUrl = 'https://api.discogs.com')
    {
        $this->requestHandler = $requestHandler;
        $this->baseUrl = $baseUrl;
    }

    public function getRelease(int $id, ?string $currency = null): Release
    {
        $url = sprintf('%s/releases/%d', $this->baseUrl, $id);
        $options = [];
        if ($currency) {
            $options['query'] = ['curr_abbr' => $currency];
        }
        $response = $this->requestHandler->get($url, $options);
        $data = $response->toArray(false);

        return Release::fromArray($data);
    }

    public function getReleaseStats(int $releaseId): array
    {
        $url = sprintf('%s/releases/%d/stats', $this->baseUrl, $releaseId);

        return $this->requestHandler->get($url)->toArray(false);
    }

    public function getReleaseRating(int $releaseId, string $username): array
    {
        $url = sprintf('%s/releases/%d/rating/%s', $this->baseUrl, $releaseId, rawurlencode($username));

        return $this->requestHandler->get($url)->toArray(false);
    }

    public function setReleaseRating(int $releaseId, int $rating, ?string $username = null): void
    {
        $url = sprintf('%s/releases/%d/rating', $this->baseUrl, $releaseId);
        $body = ['rating' => $rating];
        if ($username) {
            $body['username'] = $username;
        }
        $response = $this->requestHandler->put($url, [
            'json' => $body,
        ]);
        $response->getStatusCode(); // Should be 200 or 201
    }

    public function deleteReleaseRating(int $releaseId, string $username): void
    {
        $url = sprintf('%s/releases/%d/rating/%s', $this->baseUrl, $releaseId, rawurlencode($username));
        $response = $this->requestHandler->delete($url);
        $response->getStatusCode(); // Should be 204
    }
}
