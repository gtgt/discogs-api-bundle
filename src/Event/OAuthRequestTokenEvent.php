<?php

declare(strict_types = 1);

namespace DiscogsApiBundle\Event;

use League\OAuth1\Client\Credentials\TemporaryCredentials;
use Symfony\Contracts\EventDispatcher\Event;

class OAuthRequestTokenEvent extends Event
{
    private TemporaryCredentials $temporaryCredentials;

    private ?string $callbackUrl;

    public function __construct(TemporaryCredentials $temporaryCredentials, ?string $callbackUrl = null)
    {
        $this->temporaryCredentials = $temporaryCredentials;
        $this->callbackUrl = $callbackUrl;
    }

    public function getTemporaryCredentials(): TemporaryCredentials
    {
        return $this->temporaryCredentials;
    }

    public function getCallbackUrl(): ?string
    {
        return $this->callbackUrl;
    }
}
