<?php

declare(strict_types=1);

namespace DiscogsApiBundle\Client\Request;

use DiscogsApiBundle\Client\Response\PaginatedResponse;
use Symfony\Contracts\HttpClient\ResponseInterface;

class RequestFactory {
    public static function createPaginatedResponse(array $data, ResponseInterface $response): PaginatedResponse
    {
        $page = (int)($data['pagination']['page'] ?? 1);
        $pages = (int)($data['pagination']['pages'] ?? 1);
        $perPage = (int)($data['pagination']['per_page'] ?? count($data['releases'] ?? $data['artists'] ?? $data['results'] ?? []));
        $nextPageUrl = $data['pagination']['urls']['next'] ?? null;
        $prevPageUrl = $data['pagination']['urls']['previous'] ?? null;

        // Determine items key based on data structure
        $items = $data['releases'] ?? $data['artists'] ?? $data['results'] ?? [];

        return new PaginatedResponse(
            $items,
            $page,
            $pages,
            $perPage,
            $nextPageUrl,
            $prevPageUrl,
            $data['pagination']['urls']['first'] ?? null,
            $data['pagination']['urls']['last'] ?? null,
        );
    }
}
