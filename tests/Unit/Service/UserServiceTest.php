<?php

namespace DiscogsApiBundle\Tests\Unit\Service;

use DiscogsApiBundle\Tests\Unit\UnitTestCase;
use DiscogsApiBundle\Service\UserService;
use DiscogsApiBundle\Model\User;
use DiscogsApiBundle\Client\Request\RequestHandler;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class UserServiceTest extends UnitTestCase
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

    public function testGetIdentity(): void
    {
        $data = [
            'id' => 1,
            'username' => 'testuser',
            'name' => 'Test User',
            'profile' => 'My profile',
            'location' => 'Berlin, Germany',
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
            ->with('GET', 'https://api.discogs.com/oauth/identity', [
                'headers' => ['User-Agent' => 'test-agent']
            ])
            ->willReturn($response);

        $service = new UserService($this->requestHandler);

        $user = $service->getIdentity();

        $this->assertInstanceOf(User::class, $user);
        $this->assertSame('testuser', $user->username);
        $this->assertSame('Test User', $user->name);
    }

    public function testGetUser(): void
    {
        $data = [
            'id' => 2,
            'username' => 'otheruser',
            'name' => 'Other User',
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
            ->with('GET', 'https://api.discogs.com/users/otheruser', [
                'headers' => ['User-Agent' => 'test-agent']
            ])
            ->willReturn($response);

        $service = new UserService($this->requestHandler);

        $user = $service->getUser('otheruser');

        $this->assertSame('otheruser', $user->username);
        $this->assertSame('Other User', $user->name);
    }
}
