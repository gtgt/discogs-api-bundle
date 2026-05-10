<?php

declare(strict_types = 1);

namespace DiscogsApiBundle\Client\Response;

use ArrayIterator;
use Countable;
use IteratorAggregate;

class PaginatedResponse implements IteratorAggregate, Countable
{
    private array $items;

    private int $page;

    private int $pages;

    private int $perPage;

    private ?string $nextPageUrl;

    private ?string $prevPageUrl;

    private ?string $firstPageUrl;

    private ?string $lastPageUrl;

    public function __construct(
        array $items,
        int $page,
        int $pages,
        int $perPage,
        ?string $nextPageUrl = null,
        ?string $prevPageUrl = null,
        ?string $firstPageUrl = null,
        ?string $lastPageUrl = null
    ) {
        $this->items = $items;
        $this->page = $page;
        $this->pages = $pages;
        $this->perPage = $perPage;
        $this->nextPageUrl = $nextPageUrl;
        $this->prevPageUrl = $prevPageUrl;
        $this->firstPageUrl = $firstPageUrl;
        $this->lastPageUrl = $lastPageUrl;
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function getPages(): int
    {
        return $this->pages;
    }

    public function getPerPage(): int
    {
        return $this->perPage;
    }

    public function hasNextPage(): bool
    {
        return $this->page < $this->pages;
    }

    public function hasPrevPage(): bool
    {
        return $this->page > 1;
    }

    public function getNextPage(): ?int
    {
        return $this->hasNextPage() ? $this->page + 1 : null;
    }

    public function getPrevPage(): ?int
    {
        return $this->hasPrevPage() ? $this->page - 1 : null;
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->items);
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function getNextPageUrl(): ?string
    {
        return $this->nextPageUrl;
    }

    public function getPrevPageUrl(): ?string
    {
        return $this->prevPageUrl;
    }
}
