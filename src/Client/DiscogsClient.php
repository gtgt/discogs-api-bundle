<?php

declare(strict_types=1);

namespace DiscogsApiBundle\Client;

use DiscogsApiBundle\Client\Response\PaginatedResponse;
use DiscogsApiBundle\Service\ArtistService;
use DiscogsApiBundle\Service\CollectionService;
use DiscogsApiBundle\Service\InventoryService;
use DiscogsApiBundle\Service\LabelService;
use DiscogsApiBundle\Service\MarketplaceService;
use DiscogsApiBundle\Service\MasterService;
use DiscogsApiBundle\Service\OrderService;
use DiscogsApiBundle\Service\ReleaseService;
use DiscogsApiBundle\Service\SearchService;
use DiscogsApiBundle\Service\UserService;
use DiscogsApiBundle\Service\WantlistService;

class DiscogsClient {
    public function __construct(
        private ArtistService $artistService,
        private ReleaseService $releaseService,
        private MasterService $masterService,
        private LabelService $labelService,
        private UserService $userService,
        private CollectionService $collectionService,
        private WantlistService $wantlistService,
        private MarketplaceService $marketplaceService,
        private InventoryService $inventoryService,
        private OrderService $orderService,
        private SearchService $searchService,
    ) {}

    // Artists
    public function getArtist(int $id)
    {
        return $this->artistService->getArtist($id);
    }

    public function getArtistReleases(int $artistId, array $options = [])
    {
        return $this->artistService->getArtistReleases($artistId, $options);
    }

    // Releases
    public function getRelease(int $id, ?string $currency = null)
    {
        return $this->releaseService->getRelease($id, $currency);
    }

    public function getReleaseStats(int $releaseId)
    {
        return $this->releaseService->getReleaseStats($releaseId);
    }

    public function setReleaseRating(int $releaseId, int $rating, ?string $username = null)
    {
        $this->releaseService->setReleaseRating($releaseId, $rating, $username);
    }

    public function deleteReleaseRating(int $releaseId, string $username)
    {
        $this->releaseService->deleteReleaseRating($releaseId, $username);
    }

    public function getMaster(int $id, array $options = [])
    {
        return $this->masterService->getMaster($id, $options);
    }

    // Masters

    public function getMasterVersions(int $masterId, array $options = [])
    {
        return $this->masterService->getMasterVersions($masterId, $options);
    }

    public function getLabel(int $id, array $options = [])
    {
        return $this->labelService->getLabel($id, $options);
    }

    // Labels

    public function getLabelReleases(int $labelId, array $options = [])
    {
        return $this->labelService->getLabelReleases($labelId, $options);
    }

    public function getIdentity()
    {
        return $this->userService->getIdentity();
    }

    // Users

    public function getUser(string $username)
    {
        return $this->userService->getUser($username);
    }

    public function getCollection(string $username, int $page = 1, int $perPage = 50)
    {
        return $this->collectionService->getCollection($username, $page, $perPage);
    }

    // Collection

    public function getCollectionFolders(string $username)
    {
        return $this->collectionService->getCollectionFolders($username);
    }

    public function getCollectionFolder(string $username, int $folderId)
    {
        return $this->collectionService->getCollectionFolder($username, $folderId);
    }

    public function getFolderReleases(string $username, int $folderId, array $options = [])
    {
        return $this->collectionService->getFolderReleases($username, $folderId, $options);
    }

    public function addToCollection(string $username, int $releaseId, ?int $folderId = null, ?int $rating = null, ?string $notes = null)
    {
        $this->collectionService->addToCollection($username, $releaseId, $folderId, $rating, $notes);
    }

    public function removeFromCollection(string $username, int $releaseId, ?int $folderId = null)
    {
        $this->collectionService->removeFromCollection($username, $releaseId, $folderId);
    }

    public function getReleaseRatingInCollection(string $username, int $releaseId)
    {
        return $this->collectionService->getReleaseRating($username, $releaseId);
    }

    public function getReleaseRating(int $releaseId, string $username)
    {
        return $this->releaseService->getReleaseRating($releaseId, $username);
    }

    // Wantlist

    public function getWantlist(string $username, array $options = [])
    {
        return $this->wantlistService->getWantlist($username, $options);
    }

    public function addToWantlist(string $username, int $releaseId, array $data = [])
    {
        $this->wantlistService->addToWantlist($username, $releaseId, $data);
    }

    public function removeFromWantlist(string $username, int $releaseId)
    {
        $this->wantlistService->removeFromWantlist($username, $releaseId);
    }

    public function updateWantlistItem(string $username, int $releaseId, array $data)
    {
        $this->wantlistService->updateWantlistItem($username, $releaseId, $data);
    }

    // Marketplace/Inventory
    public function getInventory(?string $username = null, array $options = [])
    {
        return $this->inventoryService->getInventory($username, $options);
    }

    public function getListing(int|string $listingId, ?string $currency = null)
    {
        return $this->inventoryService->getListing($listingId, $currency);
    }

    public function createListing(int $releaseId, string $condition, float $price, array $options = [])
    {
        return $this->inventoryService->createListing($releaseId, $condition, $price, $options);
    }

    public function updateListing(int|string $listingId, string $condition, float $price, array $options = [])
    {
        return $this->inventoryService->updateListing($listingId, $condition, $price, $options);
    }

    public function deleteListing(int|string $listingId)
    {
        $this->inventoryService->deleteListing($listingId);
    }

    // Orders
    public function getOrders(array $options = [])
    {
        return $this->orderService->getOrders($options);
    }

    public function getOrder(string $orderId)
    {
        return $this->orderService->getOrder($orderId);
    }

    public function updateOrder(string $orderId, string $status, array $options = [])
    {
        return $this->orderService->updateOrder($orderId, $status, $options);
    }

    public function getOrderMessages(string $orderId, array $options = [])
    {
        return $this->orderService->getOrderMessages($orderId, $options);
    }

    public function addOrderMessage(string $orderId, string $message)
    {
        return $this->orderService->addOrderMessage($orderId, $message);
    }

    // Search
    public function search(string $query, array $options = []): PaginatedResponse
    {
        return $this->searchService->search($query, $options);
    }

    public function searchArtists(string $query, array $options = []): PaginatedResponse
    {
        return $this->searchService->searchArtists($query, $options);
    }

    public function searchReleases(string $query, array $options = []): PaginatedResponse
    {
        return $this->searchService->searchReleases($query, $options);
    }

    public function searchLabels(string $query, array $options = []): PaginatedResponse
    {
        return $this->searchService->searchLabels($query, $options);
    }

    public function searchMasters(string $query, array $options = []): PaginatedResponse
    {
        return $this->searchService->searchMasters($query, $options);
    }
}
