# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Initial release structure
- Core models: Artist, Release, Master, Label
- User/Collection models: User, CollectionFolder, CollectionItem
- Marketplace models: Listing, Order, OrderMessage
- Community models: Rating, Stats, Contributor
- Services: ArtistService, ReleaseService, MasterService, LabelService, UserService, CollectionService, WantlistService, MarketplaceService, InventoryService, OrderService, SearchService
- HTTP client with authentication support (UserToken + OAuth1)
- Pagination support via PaginatedResponse
- Event dispatching (request/response/error/rate_limit)
- Optional PSR-6 caching with per-endpoint TTL
- OAuthController for OAuth 1.0a flow
- Error handling with typed exceptions
- Integration tests with live API support (--live flag)

### Changed

### Deprecated

### Removed

### Fixed

### Security

## [1.0.0] - YYYY-MM-DD

First stable release.
