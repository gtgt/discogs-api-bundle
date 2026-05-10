<?php

namespace Tamash\DiscogsApiBundle\Tests\Unit\Service;

use Tamash\DiscogsApiBundle\Tests\Unit\UnitTestCase;
use Tamash\DiscogsApiBundle\Service\UserService;
use Tamash\DiscogsApiBundle\Model\User;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response;

class UserServiceTest extends UnitTestCase
{
    public function testGetIdentity(): void
    {
        $data = [
            'id' => 1,
            'username' => 'testuser',
            'name' => 'Test User',
            'profile' => 'My profile',
            'location' => 'Berlin, Germany',
        ];

        $client = new MockHttpClient(fn () => new Response($data, 200));
        $service = new UserService($client);

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

        $client = new MockHttpClient(fn () => new Response($data, 200));
        $service = new UserService($client);

        $user = $service->getUser('otheruser');

        $this->assertSame('otheruser', $user->username);
    }
}
