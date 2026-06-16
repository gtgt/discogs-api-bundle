<?php

declare(strict_types=1);

namespace DiscogsApiBundle\Event;

use League\OAuth1\Client\Credentials\TokenCredentials;
use Symfony\Contracts\EventDispatcher\Event;

class OAuthAccessTokenEvent extends Event {
    private TokenCredentials $tokenCredentials;

    public function __construct(TokenCredentials $tokenCredentials)
    {
        $this->tokenCredentials = $tokenCredentials;
    }

    public function getTokenCredentials(): TokenCredentials
    {
        return $this->tokenCredentials;
    }
}
