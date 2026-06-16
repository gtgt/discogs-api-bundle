<?php

declare(strict_types=1);

namespace DiscogsApiBundle\Service;

use DiscogsApiBundle\Client\Request\RequestHandler;
use DiscogsApiBundle\Client\Response\PaginatedResponse;
use DiscogsApiBundle\Exception\NotFoundException;
use DiscogsApiBundle\Model\Collection\CollectionFolder;

class CollectionService {
    private RequestHandler $requestHandler;

    private string $baseUrl;

    public function __construct(RequestHandler $requestHandler, string $baseUrl = 'https://api.discogs.com')
    {
        $this->requestHandler = $requestHandler;
        $this->baseUrl = $baseUrl;
    }

    public function getCollection(string $username, int $page = 1, int $perPage = 50): PaginatedResponse
    {
        $url = sprintf('%s/users/%s/collection/folders', $this->baseUrl, rawurlencode($username));
        $response = $this->requestHandler->get($url, [
            'query' => ['page' => $page, 'per_page' => $perPage],
        ]);
        $data = $response->toArray(false);

        return \DiscogsApiBundle\Client\Request\RequestFactory::createPaginatedResponse($data, $response);
    }

    public function getCollectionFolders(string $username): array
    {
        $url = sprintf('%s/users/%s/collection/folders', $this->baseUrl, rawurlencode($username));
        $response = $this->requestHandler->get($url);
        $data = $response->toArray(false);

        $folders = [];
        foreach ($data['folders'] ?? [] as $folderData) {
            $folders[] = CollectionFolder::fromArray($folderData);
        }

        return $folders;
    }

    public function getCollectionFolder(string $username, int $folderId): CollectionFolder
    {
        $url = sprintf('%s/users/%s/collection/folders/%d', $this->baseUrl, rawurlencode($username), $folderId);
        $response = $this->requestHandler->get($url);
        $data = $response->toArray(false);

        return CollectionFolder::fromArray($data);
    }

    public function getFolderReleases(string $username, int $folderId, array $options = []): PaginatedResponse
    {
        $url = sprintf('%s/users/%s/collection/folders/%d/releases', $this->baseUrl, rawurlencode($username), $folderId);
        $response = $this->requestHandler->get($url, ['query' => $options]);
        $data = $response->toArray(false);

        return \DiscogsApiBundle\Client\Request\RequestFactory::createPaginatedResponse($data, $response);
    }

    public function addToCollection(string $username, int $releaseId, ?int $folderId = null, ?int $rating = null, ?string $notes = null): void
    {
        $folderId = $folderId ?? 0; // 0 = root folder
        $url = sprintf('%s/users/%s/collection/folders/%d/releases/%d', $this->baseUrl, rawurlencode($username), $folderId, $releaseId);
        $body = [];
        if ($rating !== null) {
            $body['rating'] = $rating;
        }
        if ($notes !== null) {
            $body['notes'] = $notes;
        }

        $response = $this->requestHandler->post($url, [
            'json' => $body,
        ]);
        $response->getStatusCode(); // Should be 201
    }

    public function removeFromCollection(string $username, int $releaseId, ?int $folderId = null): void
    {
        $folderId = $folderId ?? 0;
        $url = sprintf('%s/users/%s/collection/folders/%d/releases/%d', $this->baseUrl, rawurlencode($username), $folderId, $releaseId);
        $response = $this->requestHandler->delete($url);
        $response->getStatusCode(); // Should be 204
    }

    public function getReleaseRating(string $username, int $releaseId): ?int
    {
        try {
            $url = sprintf('%s/users/%s/collection/releases/%d/rating', $this->baseUrl, rawurlencode($username), $releaseId);
            $response = $this->requestHandler->get($url);
            $data = $response->toArray(false);

            return isset($data['rating']) ? (int)$data['rating'] : null;
        } catch (NotFoundException $e) {
            return null;
        }
    }
}
