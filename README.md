# Discogs API Bundle

A Symfony Bundle providing a complete, type-safe API client for [Discogs.com v2.0 API](https://www.discogs.com/developers/) with OAuth 1.0a authentication support.

## Features

- ✅ Complete API coverage: Database (artists, releases, labels, masters), User Collections, Wantlists, Marketplace (listings, orders)
- ✅ Dual authentication: User-Token (simple) and OAuth 1.0a (full flow)
- ✅ Value-object models with proper types and immutability
- ✅ Pagination support with iterator interface
- ✅ Rate limit handling and automatic retry with exponential backoff
- ✅ Optional PSR-6 caching with per-endpoint TTL
- ✅ Event dispatching for extensibility (logging, monitoring)
- ✅ Symfony 8.0+ compatible, follows bundle best practices
- ✅ Comprehensive error handling with typed exceptions

## Installation

```bash
composer require tamas-gere/discogs-api-bundle
```

### Register the bundle

For Symfony Flex projects (recommended): the bundle is auto-registered.

For manual registration, add to `config/bundles.php`:

```php
return [
    // ...
    Tamash\DiscogsApiBundle\DiscogsApiBundle::class => ['all' => true],
];
```

### Configure

Copy configuration to your project:

```bash
cp vendor/tamas-gere/discogs-api-bundle/config/packages/discogs_api.yaml config/packages/discogs_api.yaml
```

Edit `config/packages/discogs_api.yaml`:

```yaml
discogs_api:
    user_agent: 'MyApp/1.0'  # Required - unique identifier for your app

    # Option 1: Personal token (simplest)
    user_token:
        token: '%env(DISCOGS_USER_TOKEN)%'

    # Option 2: OAuth 1.0a (for multi-user apps)
    # oauth1:
    #     consumer_key: '%env(DISCOGS_CONSUMER_KEY)%'
    #     consumer_secret: '%env(DISCOGS_CONSUMER_SECRET)%'
```

Get your credentials from [Discogs Developer Settings](https://www.discogs.com/settings/developers).

Set environment variables in `.env`:

```dotenv
# Option 1 - Simple token
DISCOGS_USER_TOKEN=your_user_token_here

# Option 2 - OAuth app credentials
DISCOGS_CONSUMER_KEY=your_consumer_key
DISCOGS_CONSUMER_SECRET=your_consumer_secret
```

## Usage

### Basic: Personal Token

```php
// In controller or service
public function __construct(private DiscogsClient $discogsClient) {}

// Get a release
$release = $this->discogsClient->getRelease(12345);
echo $release->title;

// Search
$results = $this->discogsClient->search('Daft Punk', [
    'type' => 'artist',
    'per_page' => 10,
]);
foreach ($results as $result) {
    echo $result->title;
}

// Get your identity
$user = $this->discogsClient->getIdentity();

// Add to collection
$this->discogsClient->addToCollection('your_username', $releaseId, folderId: 0, rating: 5, notes: 'Want this');
```

### Advanced: OAuth 1.0a Flow

```php
// Step 1: Redirect user to authorization
$authUrl = $this->generateUrl('discogs_api_oauth_request_token');
// Or call OAuthController::requestToken() directly
// User redirected to Discogs, authorizes app

// Step 2: Handle callback at route "discogs_api_oauth_callback"
// Controller receives token and verifier, exchanges for access token
// Store token credentials in database (user_id -> token, secret)

// Step 3: Configure with stored tokens
// In config/services.yaml:
discogs_api:
    oauth1:
        consumer_key: '%env(DISCOGS_CONSUMER_KEY)%'
        consumer_secret: '%env(DISCOGS_CONSUMER_SECRET)%'
        token: '%user.discogs_token%'  # stored per-user token
        token_secret: '%user.discogs_token_secret%'

// Now all API calls act on behalf of that user
$collection = $discogsClient->getCollection($username);
```

### OAuth Controller Routes (optional)

Enable routes in `config/routes.yaml`:

```yaml
discogs_api_oauth:
    resource: '@DiscogsApiBundle/Controller/OAuthController.php'
    type: annotation
```

Routes:
- `GET /oauth/request-token` - Get request token & redirect to Discogs
- `GET /oauth/callback` - OAuth callback from Discogs
- `GET /oauth/token` - Inspect current session's token
- `POST /oauth/logout` - Clear OAuth session

### Services

Inject any service individually:

```php
use Tamash\DiscogsApiBundle\Service\ArtistService;

public function __construct(private ArtistService $artistService) {}

$artist = $this->artistService->getArtist(12345);
$releases = $this->artistService->getArtistReleases(12345, ['per_page' => 20]);
```

Available services:
- `ArtistService` - artists, artist releases, search (artists)
- `ReleaseService` - releases, ratings, stats
- `MasterService` - master releases, versions
- `LabelService` - labels, label releases
- `UserService` - user identity, user profiles
- `CollectionService` - folders, collection CRUD, ratings
- `WantlistService` - wantlist CRUD
- `MarketplaceService` - listings CRUD, orders, messages
- `InventoryService` - inventory listings CRUD
- `OrderService` - order management, messages
- `SearchService` - full-text search across all types

### Pagination

List methods return `PaginatedResponse`:

```php
$paginated = $discogsClient->getCollection('username');
foreach ($paginated as $item) {
    // Iterate items
}
echo $paginated->count();       // items on current page
echo $paginated->getPage();     // current page
echo $paginated->getPages();    // total pages
if ($paginated->hasNextPage()) {
    $page = $paginated->getNextPage();
}
```

### Error Handling

All API errors throw exceptions:

```php
use Tamash\DiscogsApiBundle\Exception\{
    DiscogsApiException,
    RateLimitException,
    AuthenticationException,
    NotFoundException,
    ValidationException
};

try {
    $release = $discogsClient->getRelease(999999999);
} catch (NotFoundException $e) {
    // 404 - resource not found
} catch (AuthenticationException $e) {
    // 401/403 - check your token/keys
} catch (RateLimitException $e) {
    // 429 - rate limit exceeded
    $retryAfter = $e->getRetryAfter();
} catch (DiscogsApiException $e) {
    // other API errors
}
```

### Caching (optional)

Enable caching in config:

```yaml
discogs_api:
    cache:
        enabled: true
        pool: 'cache.app'  # any PSR-6 pool
        ttl:
            artists: 86400   # 24h
            releases: 43200  # 12h
```

Cache keys are auto-purged on write operations (add/remove collection, create/update listing).

### Events (optional)

Subscribe to events for logging/metrics:

```php
use Tamash\DiscogsApiBundle\Event\{
    RequestBeforeEvent,
    ResponseEvent,
    ErrorEvent,
    RateLimitEvent
};
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class LoggingSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            'discogs_api.request.before' => 'onRequestBefore',
            'discogs_api.request.after' => 'onRequestAfter',
            'discogs_api.error' => 'onError',
            'discogs_api.rate_limit.exceeded' => 'onRateLimit',
        ];
    }

    public function onRequestBefore(RequestBeforeEvent $event): void
    {
        // Log outgoing request
    }

    public function onError(ErrorEvent $event): void
    {
        // Log errors
    }
}
```

## Testing

### Unit Tests

```bash
vendor/bin/phpunit tests/Unit
```

### Integration Tests (live API)

By default, live tests are skipped. To run:

```bash
# Using env var
DISCOGS_TEST_LIVE=1 vendor/bin/phpunit tests/Integration

# Or with --live flag (if using custom test runner)
vendor/bin/phpunit --group live --live
```

Configuration for live tests in `phpunit.xml.dist`:

```xml
<php>
    <env name="DISCOGS_USER_TOKEN" value="test_token_here"/>
    <env name="DISCOGS_CONSUMER_KEY" value="key"/>
    <env name="DISCOGS_CONSUMER_SECRET" value="secret"/>
</php>
```

## Rate Limits

Discogs enforces rate limits:
- Unauthenticated: 60 requests/minute
- Authenticated: 5000 requests/minute

The bundle automatically tracks `X-Ratelimit-Remaining` and can retry on 429 with `retry_on_rate_limit: true`. Configure `max_retries` for resilience.

## Contributing

Contributions welcome! Please read [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## License

MIT License. See [LICENSE](LICENSE) file.

## Resources

- [Discogs API Docs](https://www.discogs.com/developers/)
- [Symfony Bundle Best Practices](https://symfony.com/doc/current/bundles.html)
- [OAuth 1.0 Client](https://github.com/thephpleague/oauth1-client)
- [Report Issues](https://github.com/your-username/discogs-api-bundle/issues)
