<?php

declare(strict_types=1);

/**
 * Demo script showing Discogs API Bundle usage in a standalone Symfony app
 *
 * This file demonstrates the common usage patterns.
 *
 * Requirements:
 * - composer install
 * - Symfony application with bundle registered
 * - Valid DISCOGS_USER_TOKEN or OAuth credentials in .env
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tamash\DiscogsApiBundle\DiscogsApiBundle;
use Tamash\DiscogsApiBundle\Client\DiscogsClient;

// Build container
$container = new ContainerBuilder();
$container->registerExtension(new DiscogsApiBundle());
$container->loadFromExtension('discogs_api', [
    'user_agent' => 'DiscogsDemo/1.0',
    'user_token' => [
        'token' => getenv('DISCOGS_USER_TOKEN') ?: 'demo_token',
    ],
    'oauth1' => [
        'consumer_key' => getenv('DISCOGS_CONSUMER_KEY') ?: '',
        'consumer_secret' => getenv('DISCOGS_CONSUMER_SECRET') ?: '',
    ],
]);
$container->compile();

// Get client
$client = $container->get(DiscogsClient::class);

echo "=== Discogs API Demo ===\n\n";

// Example 1: Get a release
try {
    echo "Fetching release 249504 (Daft Punk - Homework)...\n";
    $release = $client->getRelease(249504);
    echo "Title: {$release->title}\n";
    echo "Year: " . ($release->year ?? 'N/A') . "\n";
    echo "Country: " . ($release->country ?? 'N/A') . "\n";
    echo "Genres: " . implode(', ', $release->genres) . "\n\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n\n";
}

// Example 2: Search for artists
try {
    echo "Searching for 'Daft Punk'...\n";
    $results = $client->searchArtists('Daft Punk', ['per_page' => 3]);
    foreach ($results as $result) {
        echo "- {$result->title} (ID: {$result->id})\n";
    }
    echo "\n";
} catch (Exception $e) {
    echo "Search error: " . $e->getMessage() . "\n\n";
}

// Example 3: Get artist
try {
    echo "Fetching artist 1 (Daft Punk)...\n";
    $artist = $client->getArtist(1);
    echo "Name: {$artist->name}\n";
    echo "Real name: " . ($artist->realname ?? 'N/A') . "\n";
    echo "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n\n";
}

// Example 4: Pagination
try {
    echo "Fetching collection (paginated)...\n";
    $collection = $client->getCollection('me', 1, 10);
    echo "Page: " . $collection->getPage() . "/" . $collection->getPages() . "\n";
    echo "Items on page: " . $collection->count() . "\n";
    if ($collection->hasNextPage()) {
        echo "Next page: " . $collection->getNextPage() . "\n";
    }
    echo "\n";
} catch (Exception $e) {
    echo "Collection error: " . $e->getMessage() . "\n\n";
}

echo "=== Demo Complete ===\n";
