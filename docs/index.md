# Discogs API Bundle Documentation

## Introduction

This bundle provides a complete, type-safe API client for the [Discogs REST API v2.0](https://www.discogs.com/developers/). It is designed for Symfony 8.0+ applications.

## Table of Contents

- [Installation](#installation)
- [Configuration](#configuration)
- [Authentication](#authentication)
- [Usage Examples](#usage-examples)
- [Services](#services)
- [Models](#models)
- [Events](#events)
- [Caching](#caching)
- [Error Handling](#error-handling)
- [Testing](#testing)
- [Contributing](#contributing)

## Installation

See the [README.md](../README.md) for installation instructions.

## Configuration

Full configuration reference:

```yaml
discogs_api:
    user_agent: 'MyApp/1.0'           # Required
    base_url: 'https://api.discogs.com'
    timeout: 30

    user_token:
        token: '%env(DISCOGS_TOKEN)%'

    oauth1:
        consumer_key: '%env(DISCOGS_CONSUMER_KEY)%'
        consumer_secret: '%env(DISCOGS_CONSUMER_SECRET)%'
        token: '%env(DISCOGS_TOKEN)%'          # optional
        token_secret: '%env(DISCOGS_TOKEN_SECRET)%'  # optional
        callback_url: 'http://localhost/oauth/callback'

    cache:
        enabled: false
        pool: 'cache.app'
        ttl:
            artists: 3600
            releases: 1800
            masters: 3600
            labels: 3600
            collection: 300
            wantlist: 300
            marketplace: 60

    enable_rate_limit_header: true
    retry_on_rate_limit: false
    max_retries: 3
    dispatch_events: false
```

## Authentication

### User Token (Simple)

Get a personal token from your [Discogs Developer Settings](https://www.discogs.com/settings/developers). This allows your app to access your own data.

```yaml
discogs_api:
    user_token:
        token: '%env(DISCOGS_USER_TOKEN)%'
```

**Use case:** Personal scripts, management tools, data backup.

### OAuth 1.0a (Full Flow)

Register an OAuth application in Discogs to obtain consumer key/secret. This allows any user to authorize your app.

**OAuth Flow:**
1. User clicks "Connect with Discogs"
2. Redirect to: `GET /oauth/request-token` (handled by OAuthController)
3. User authorizes on Discogs
4. Discogs redirects to your callback URL with `oauth_token` and `oauth_verifier`
5. Exchange for access token (handled by callback controller)
6. Store tokens (in DB) and use for subsequent API calls

Configure with stored tokens per user:
```yaml
discogs_api:
    oauth1:
        consumer_key: '%env(DISCOGS_CONSUMER_KEY)%'
        consumer_secret: '%env(DISCOGS_CONSUMER_SECRET)%'
        token: 'stored_user_token'        # fetched from database
        token_secret: 'stored_user_secret' # fetched from database
```

The `OAuthController` provides:
- `GET /oauth/request-token` - Start OAuth flow
- `GET /oauth/callback` - Handle callback (auto)
- `GET /oauth/token` - View current session token
- `POST /oauth/logout` - Clear session tokens

## Models

All models extend `AbstractModel` and provide a `fromArray()` factory method. They are immutable value objects.

### Database Models

**Artist**: id, name, realname, profile, profileViews, thumb, resourceUrl, uri, aliases, members, urls, nameVariations, images, dataQuality.

**Release**: id, title, description, dataQuality, year, released, country, genres, styles, labels, artists, master, mainReleaseId, formats, formatQuantity, catno, barcode, thumb, coverImage, images, videos, companies, identifiers, tracklist, extraArtists, notes, community, statistics, resourceUrl.

**Master**: id, title, description, dataQuality, year, released, genres, styles, artists, versions, thumb, coverImage, resourceUrl, mainRelease.

**Label**: id, name, contactInfo, profile, profileViews, thumb, resourceUrl, uri, sublabels, urls, images, dataQuality.

### User/Collection Models

**User**: id, username, name, avatarUrl, resourceUrl, profile, location, website, joinDate, wantlist, collection, folderIds.

**CollectionFolder**: id, name, count, resourceUrl.

**CollectionItem**: id, title, thumb, artists, rating, notes, dateAdded, resourceUrl.

### Marketplace Models

**Listing**: id, releaseInfo, status, price, currency, condition, sleeveCondition, comments, allowOffers, seller, location, weight, formatQuantity, externalId, posted, expires, shipsWithin, images.

**Order**: id, status, buyer, seller, total, currency, created, lastActivity, items.

**OrderMessage**: id, message, username, avatarUrl, created.

### Community Models

**ReleaseCommunity**: status, submitterUsername, submitterResourceUrl, contributors, rating, stats, dataQuality.

**Rating**: average, count, userRating.

**Stats**: inCollection, inWantlist, haves, wants.

**Contributor**: username, resourceUrl.

## Services

All services are auto-wired. Inject whichever service you need:

### ArtistService

```php
public function getArtist(int $id): Artist
public function getArtistReleases(int $artistId, array $options = []): PaginatedResponse
public function search(string $query, array $options = []): PaginatedResponse
```

### ReleaseService

```php
public function getRelease(int $id, ?string $currency = null): Release
public function getReleaseStats(int $releaseId): array
public function getReleaseRating(int $releaseId, string $username): array
public function setReleaseRating(int $releaseId, int $rating, ?string $username = null): void
public function deleteReleaseRating(int $releaseId, string $username): void
```

### SearchService

```php
public function search(string $query, array $options = []): PaginatedResponse
public function searchArtists(string $query, array $options = []): PaginatedResponse
public function searchReleases(string $query, array $options = []): PaginatedResponse
```

Search options: `type`, `title`, `release_title`, `credit`, `artist`, `label`, `genre`, `style`, `year`, `country`, `format`, `catno`, `barcode`, `track`, `submitter`, `sort`, `sort_order`, `page`, `per_page`.

### CollectionService

```php
public function getCollection(string $username, int $page = 1, int $perPage = 50): PaginatedResponse
public function getCollectionFolders(string $username): array<CollectionFolder>
public function getCollectionFolder(string $username, int $folderId): CollectionFolder
public function getFolderReleases(string $username, int $folderId, array $options = []): PaginatedResponse
public function addToCollection(string $username, int $releaseId, ?int $folderId = null, ?int $rating = null, ?string $notes = null): void
public function removeFromCollection(string $username, int $releaseId, ?int $folderId = null): void
```

### WantlistService

```php
public function getWantlist(string $username, array $options = []): PaginatedResponse
public function addToWantlist(string $username, int $releaseId, array $data = []): void
public function removeFromWantlist(string $username, int $releaseId): void
public function updateWantlistItem(string $username, int $releaseId, array $data): void
```

### MarketplaceService & InventoryService

```php
// Listings
public function getInventory(?string $username = null, array $options = []): PaginatedResponse
public function getListing(int|string $listingId, ?string $currency = null): Listing
public function createListing(int $releaseId, string $condition, float $price, array $options = []): Listing
public function updateListing(int|string $listingId, string $condition, float $price, array $options = []): Listing
public function deleteListing(int|string $listingId): void

// Orders
public function getOrders(array $options = []): PaginatedResponse
public function getOrder(string $orderId): Order
public function updateOrder(string $orderId, string $status, array $options = []): Order
public function getOrderMessages(string $orderId, array $options = []): array
public function addOrderMessage(string $orderId, string $message): OrderMessage
```

## DiscogsClient Facade

For convenience, inject the single `DiscogsClient` facade which delegates to all services:

```php
use Tamash\DiscogsApiBundle\Client\DiscogsClient;

class MyService
{
    public function __construct(private DiscogsClient $discogs) {}

    public function doSomething(): void
    {
        $artist = $this->discogs->getArtist(123);
        $search = $this->discogs->search('Daft Punk');
        $collection = $this->discogs->getCollection('me');
    }
}
```

## Error Handling

Typed exceptions:

- `DiscogsApiException` (base)
- `RateLimitException` (429) - includes `getRetryAfter(): ?int`
- `AuthenticationException` (401, 403)
- `NotFoundException` (404)
- `ValidationException` (400) - includes `getErrors(): array`

All extend `DiscogsApiException` and include original response.

```php
try {
    $release = $client->getRelease(999999);
} catch (NotFoundException $e) {
    // Handle not found
} catch (RateLimitException $e) {
    $retryAfter = $e->getRetryAfter();
    // Wait and retry
}
```

## Events

Enable events in config:

```yaml
discogs_api:
    dispatch_events: true
```

Available events:

| Event Name | Class | Description |
|-------------|-------|-------------|
| `discogs_api.request.before` | RequestBeforeEvent | Before HTTP request |
| `discogs_api.request.after` | ResponseEvent | After HTTP response |
| `discogs_api.error` | ErrorEvent | On API error |
| `discogs_api.rate_limit.exceeded` | RateLimitEvent | Rate limit hit |
| `discogs_api.oauth.request_token` | OAuthRequestTokenEvent | OAuth start |
| `discogs_api.oauth.complete` | OAuthCompleteEvent | OAuth finished |

Example subscriber:

```php
use Tamash\DiscogsApiBundle\Event\RequestBeforeEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class LoggingSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            'discogs_api.request.before' => 'logRequest',
        ];
    }

    public function logRequest(RequestBeforeEvent $event): void
    {
        error_log(sprintf(
            'Discogs API: %s %s',
            $event->getMethod(),
            $event->getUrl()
        ));
    }
}
```

## Caching

Caching is opt-in:

```yaml
discogs_api:
    cache:
        enabled: true
        pool: 'cache.app'  # your PSR-6 pool
        ttl:
            artists: 3600    # Cache for 1 hour
            releases: 1800   # 30 minutes
```

Cache is automatically invalidated when you modify data (add collection item, update listing, etc.).

### Using Custom Cache Pool

Define your pool in Symfony cache config, then reference it:

```yaml
framework:
    cache:
        pools:
            discogs_cache:
                adapter: cache.adapter.filesystem
                default_lifetime: 1800

discogs_api:
    cache:
        enabled: true
        pool: 'discogs_cache'
```

## Rate Limits

- Unauthenticated: 60 requests/min
- Authenticated: 5000 requests/min

The bundle tracks rate limit headers and throws `RateLimitException` when exceeded. Set `retry_on_rate_limit: true` to automatically retry after the reset time.

```yaml
discogs_api:
    retry_on_rate_limit: true
    max_retries: 5
```

## Rate Limit Headers

When `enable_rate_limit_header: true`, the client reads:

- `X-Ratelimit-Remaining` - requests left in window
- `X-Ratelimit-Used` - requests used
- `X-Ratelimit-Reset` - Unix timestamp when window resets

Access via response headers or events.

## Contributing

See [CONTRIBUTING.md](../CONTRIBUTING.md) for development guidelines.

## License

MIT License. See [LICENSE](../LICENSE).
