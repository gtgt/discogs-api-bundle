<?php

declare(strict_types=1);

namespace Tamash\DiscogsApiBundle\Service;

use Tamash\DiscogsApiBundle\Client\Request\RequestHandler;
use Tamash\DiscogsApiBundle\Model\Master;
use Tamash\DiscogsApiBundle\Client\Response\PaginatedResponse;

class MasterService
{
    private RequestHandler $requestHandler;
    private string $baseUrl;

    public function __construct(RequestHandler $requestHandler, string $baseUrl = 'https://api.discogs.com')
    {
        $this->requestHandler = $requestHandler;
        $this->baseUrl = $baseUrl;
    }

    public function getMaster(int $id, array $options = []): Master
    {
        $url = sprintf('%s/masters/%d', $this->baseUrl, $id);
        $response = $this->requestHandler->get($url, ['query' => $options]);
        $data = $response->toArray(false);

        return Master::fromArray($data);
    }

    public function getMasterVersions(int $masterId, array $options = []): PaginatedResponse
    {
        $url = sprintf('%s/masters/%d/versions', $this->baseUrl, $masterId);
        $response = $this->requestHandler->get($url, ['query' => $options]);
        $data = $response->toArray(false);

        return \Tamash\DiscogsApiBundle\Client\Request\RequestFactory::createPaginatedResponse($data, $response);
    }
}
