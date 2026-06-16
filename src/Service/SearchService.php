<?php

declare(strict_types=1);

namespace DiscogsApiBundle\Service;

use DiscogsApiBundle\Client\Request\RequestHandler;
use DiscogsApiBundle\Client\Response\PaginatedResponse;

class SearchService {
    private RequestHandler $requestHandler;

    private string $baseUrl;

    public function __construct(RequestHandler $requestHandler, string $baseUrl = 'https://api.discogs.com')
    {
        $this->requestHandler = $requestHandler;
        $this->baseUrl = $baseUrl;
    }

    /**
     * Search for artists only
     */
    public function searchArtists(string $query, array $options = []): PaginatedResponse
    {
        $options['type'] = 'artist';

        return $this->search($query, $options);
    }

    /**
     * Search Discogs database
     *
     * @param string $query Search query
     * @param array $options {
     *
     * @return PaginatedResponse<array{type: string, id: int, title: string, thumb?: string}>
     * @var string|null $type Filter by type: 'artist', 'release', 'master', 'label'
     * @var string|null $title Filter by title
     * @var string|null $releaseTitle Filter by release title
     * @var string|null $credit Filter by credit (artist name)
     * @var string|null $artist Filter by artist
     * @var string|null $label Filter by label
     * @var string|null $genre Filter by genre
     * @var string|null $style Filter by style
     * @var int|null $year Filter by year
     * @var string|null $country Filter by country code
     * @var string|null $format Filter by format (Vinyl, CD, etc.)
     * @var int|null $catno Filter by catalog number
     * @var int|null $barcode Filter by barcode
     * @var string|null $track Filter by track name
     * @var string|null $submitter Filter by submitter username
     * @var string $sort 'artist', 'title', 'label', 'catno', 'score', 'date'
     * @var string $sort_order 'asc' or 'desc'
     * @var int $page Page number
     * @var int $per_page Results per page (default 50, max 100)
     * }
     *
     */
    public function search(string $query, array $options = []): PaginatedResponse
    {
        $url = $this->baseUrl.'/database/search';
        $queryParams = array_merge(['q' => $query], $options);

        $response = $this->requestHandler->get($url, ['query' => $queryParams]);
        $data = $response->toArray(false);

        $paginated = \DiscogsApiBundle\Client\Request\RequestFactory::createPaginatedResponse($data, $response);

        // Map results to simple DTOs (each result has type, id, title, thumb)
        // The actual model objects would require fetching each individually
        return $paginated;
    }

    /**
     * Search for releases only
     */
    public function searchReleases(string $query, array $options = []): PaginatedResponse
    {
        $options['type'] = 'release';

        return $this->search($query, $options);
    }

    /**
     * Search for labels only
     */
    public function searchLabels(string $query, array $options = []): PaginatedResponse
    {
        $options['type'] = 'label';

        return $this->search($query, $options);
    }

    /**
     * Search for masters only
     */
    public function searchMasters(string $query, array $options = []): PaginatedResponse
    {
        $options['type'] = 'master';

        return $this->search($query, $options);
    }
}
