<?php

namespace DiscogsApiBundle\Tests\Unit\Service;

use DiscogsApiBundle\Tests\Unit\UnitTestCase;
use DiscogsApiBundle\Service\LabelService;
use DiscogsApiBundle\Model\Label;
use DiscogsApiBundle\Client\Request\RequestHandler;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class LabelServiceTest extends UnitTestCase
{
    private HttpClientInterface&MockObject $httpClient;
    private RequestHandler $requestHandler;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->requestHandler = new RequestHandler(
            $this->httpClient,
            null, // authenticator
            null, // dispatcher
            'test-agent',
            false, // enableRateLimitHeader
            0, // maxRetries
            null, // cachePool
        );
    }

    public function testGetLabel(): void
    {
        $data = [
            'id' => 999,
            'name' => 'Test Label',
            'profile' => 'A test label',
            'profileviews' => 1234,
            'sublabels' => [],
            'urls' => ['http://example.com'],
        ];

        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->once())
            ->method('toArray')
            ->willReturn($data);
        $response->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(200);

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with('GET', 'https://api.discogs.com/labels/999', ['headers' => ['User-Agent' => 'test-agent'], 'query' => []])
            ->willReturn($response);

        $service = new LabelService($this->requestHandler);

        $label = $service->getLabel(999);

        $this->assertInstanceOf(Label::class, $label);
        $this->assertSame(999, $label->id);
        $this->assertSame('Test Label', $label->name);
        $this->assertSame(1234, $label->profileViews);
    }

    public function testGetLabelReleases(): void
    {
        $data = [
            'pagination' => [
                'page' => 1,
                'pages' => 2,
                'per_page' => 20,
                'urls' => [],
            ],
            'releases' => [
                ['id' => 1, 'title' => 'Release 1'],
                ['id' => 2, 'title' => 'Release 2'],
            ],
        ];

        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->once())
            ->method('toArray')
            ->willReturn($data);
        $response->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(200);

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with('GET', 'https://api.discogs.com/labels/999/releases', ['headers' => ['User-Agent' => 'test-agent'], 'query' => []])
            ->willReturn($response);

        $service = new LabelService($this->requestHandler);

        $paginated = $service->getLabelReleases(999);

        $this->assertCount(2, $paginated);
        $this->assertSame(1, $paginated->getPage());
        $this->assertSame(2, $paginated->getPages());
        $this->assertSame('Release 1', $paginated->getItems()[0]['title']);
    }
}
