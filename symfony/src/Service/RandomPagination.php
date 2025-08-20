<?php

namespace App\Service;

class RandomPagination implements \IteratorAggregate, \Countable
{
    private array $items;
    private int $totalItemCount;
    private int $currentPageNumber;
    private int $itemNumberPerPage;
    private int $pageCount;

    public function __construct(array $items, int $totalItemCount, int $currentPageNumber, int $itemNumberPerPage)
    {
        $this->items = $items;
        $this->totalItemCount = $totalItemCount;
        $this->currentPageNumber = $currentPageNumber;
        $this->itemNumberPerPage = $itemNumberPerPage;
        $this->pageCount = (int) ceil($totalItemCount / $itemNumberPerPage);
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function getTotalItemCount(): int
    {
        return $this->totalItemCount;
    }

    public function getCurrentPageNumber(): int
    {
        return $this->currentPageNumber;
    }

    public function getItemNumberPerPage(): int
    {
        return $this->itemNumberPerPage;
    }

    public function getPageCount(): int
    {
        return $this->pageCount;
    }

    public function haveToPaginate(): bool
    {
        return $this->totalItemCount > $this->itemNumberPerPage;
    }

    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->items);
    }

    public function count(): int
    {
        return count($this->items);
    }

    // Pour la compatibilité avec Twig
    public function __get(string $name)
    {
        return match ($name) {
            'totalItemCount' => $this->totalItemCount,
            'currentPageNumber' => $this->currentPageNumber,
            'itemNumberPerPage' => $this->itemNumberPerPage,
            'pageCount' => $this->pageCount,
            default => null
        };
    }

    public function __isset(string $name): bool
    {
        return in_array($name, ['totalItemCount', 'currentPageNumber', 'itemNumberPerPage', 'pageCount']);
    }
}
