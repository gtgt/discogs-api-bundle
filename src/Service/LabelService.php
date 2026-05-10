<?php

declare(strict_types=1);

namespace Tamash\DiscogsApiBundle\Service;

use Tamash\DiscogsApiBundle\Client\Request\RequestHandler;
use Tamash\DiscogsApiBundle\Model\Label;
use Tamash\DiscogsApiBundle\Client\Response\PaginatedResponse;

class LabelService
{
    private RequestHandler $requestHandler;
    private string $baseUrl;

    public function __construct(RequestHandler $requestHandler, string $baseUrl = 'https://api.discogs.com')
    {
        $this->requestHandler = $requestHandler;
        $this->baseUrl = $baseUrl;
    }

    public function getLabel(int $id, array $options = []): Label
    {
        $url = sprintf('%s/labels/%d', $this->baseUrl, $id);
        $response = $this->requestHandler->get($url, ['query' => $options]);
        $data = $response->toArray(false);

        return Label::fromArray($data);
    }

    public function getLabelReleases(int $labelId, array $options = []): PaginatedResponse
    {
        $url = sprintf('%s/labels/%d/releases', $this->baseUrl, $labelId);
        $response = $this->requestHandler->get($url, ['query' => $options]);
        $data = $response->toArray(false);

        return \Tamash\DiscogsApiBundle\Client\Request\RequestFactory::createPaginatedResponse($data, $response);
    }
}
