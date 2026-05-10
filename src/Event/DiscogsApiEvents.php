<?php

declare(strict_types=1);

namespace Tamash\DiscogsApiBundle\Event;

abstract class DiscogsApiEvents
{
    public const REQUEST_BEFORE = 'discogs_api.request.before';
    public const REQUEST_AFTER = 'discogs_api.request.after';
    public const ERROR = 'discogs_api.error';
    public const RATE_LIMIT_EXCEEDED = 'discogs_api.rate_limit.exceeded';
    public const OAUTH_REQUEST_TOKEN = 'discogs_api.oauth.request_token';
    public const OAUTH_ACCESS_TOKEN = 'discogs_api.oauth.access_token';
    public const OAUTH_COMPLETE = 'discogs_api.oauth.complete';
}
