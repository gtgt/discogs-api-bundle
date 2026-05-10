# Contributing to Discogs API Bundle

Thank you for considering contributing!

## Code of Conduct

This project adheres to the [Contributor Covenant Code of Conduct](CODE_OF_CONDUCT.md). By participating, you agree to its terms.

## How to Contribute

### Reporting Bugs

Before opening a bug report:
1. Check existing issues to avoid duplicates
2. Verify the bug exists in latest version
3. Include steps to reproduce, expected vs actual behavior
4. Include PHP version, Symfony version, bundle version

Open an issue with `bug` label.

### Suggesting Features

Feature requests are welcome. Before opening:
1. Check if feature aligns with bundle's scope (Discogs API client)
2. Search existing requests
3. Provide clear use case and example API usage

Open an issue with `enhancement` label.

### Pull Requests

1. Fork the repository
2. Create a branch from `main` (e.g., `fix/issue-123`)
3. Make your changes with clear, descriptive commits
4. Ensure code follows PSR-12 coding standards
5. Run tests: `vendor/bin/phpunit`
6. Run static analysis: `vendor/bin/phpstan analyse`
7. Run CS fixer: `vendor/bin/php-cs-fixer fix`
8. Update documentation if needed
9. Open PR with clear description and linked issue

#### Commit Message Format

```
type(scope): description

Body (optional, wrapped at 72 chars)

- bullet points
- for complex changes

Closes #123
```

Types: `feat`, `fix`, `docs`, `refactor`, `test`, `chore`, `style`

Example:
```
feat(collection): add support for collection folder CRUD

- Add getCollectionFolder() method
- Add removeFromCollection() with folder support
- Add moveBetweenFolders() - new endpoint

Closes #45
```

## Development Environment

1. Clone your fork
2. Install dependencies: `composer install`
3. Set up test Discogs credentials (optional):
   ```bash
   cp .env.example .env.test.local
   # Edit with your credentials
   ```
4. Run tests: `vendor/bin/phpunit`

### Running Specific Tests

```bash
# Unit tests only
vendor/bin/phpunit tests/Unit

# Integration tests (require live API)
DISCOGS_TEST_LIVE=1 vendor/bin/phpunit tests/Integration

# Single test
vendor/bin/phpunit tests/Unit/Model/ArtistTest.php

# With coverage
XDEBUG_MODE=coverage vendor/bin/phpunit --coverage-html coverage/
```

## Coding Standards

- Follow [PSR-12](https://www.php-fig.org/psr/psr-12/)
- Use native PHP types (no `?` without type declarations)
- Prefer readonly classes and immutable objects
- Use explicit visibility (`public`/`private`/`protected`)
- 4 space indentation, no tabs
- Trailing commas in multi-line arrays
- Typed properties and return types

### Static Analysis

We use PHPStan at level 8 (max strictness). Aim for zero errors.

```bash
vendor/bin/phpstan analyse
```

### Code Style

Run CS fixer:

```bash
vendor/bin/php-cs-fixer fix
```

## Bundle Architecture

### Directory Structure

Follow Symfony bundle conventions:

```
src/
  Model/           # Value objects (immutable)
  Service/         # Business logic, API operations
  Client/          # HTTP layer, authenticators
  Controller/      # OAuth flow handlers
  Event/           # Event classes
  Exception/       # Custom exceptions
  DependencyInjection/ # Config & DI
```

### Service Design

- Services should be focused on one domain (Artist, Release, etc.)
- Keep public methods small and testable
- Use `RequestHandler` for all HTTP calls
- Return model objects, not raw arrays
- Throw typed exceptions on errors

### Model Design

- All models extend `AbstractModel`
- Use `public readonly` properties
- One model class per resource
- `fromArray()` factory method handles mapping
- Use helper methods from `AbstractModel` (`getStringOrNull`, etc.)

## Adding New API Endpoints

1. Add fields to appropriate model class (if response includes new data)
2. Add method to relevant service
3. Add method to `DiscogsClient` facade (optional but recommended)
4. Write unit test (mock HTTP client)
5. If adding new endpoint type, also add integration test
6. Update docs

## Documentation

- Update `README.md` for user-facing changes
- Update `docs/index.md` for detailed API docs
- Add docblocks (PHPDoc) to public methods
- Use real examples in docs

## Testing Standards

- Unit tests: Mock `HttpClientInterface`, test logic
- Functional tests: Use `MockHttpClient` for response simulation
- Integration tests: Mark with `#[Group('live')]` for real API tests
- Aim for >80% coverage (bundle-original code only)

## Questions?

Open an issue or reach out to maintainers.

Thank you for contributing!
