<?php
declare(strict_types=1);

namespace Crystal\Components;

/**
 * Pager
 */
final class Pager
{
    public array $pagerItems = [];

    public array $first = [];
    public array $last = [];
    public array $previous = [];
    public array $next = [];
    public array $page = [];
    public int $totalPages = 0;

    public function __construct(string $baseUrl = '/users', int $activePage = 1, int $itemsPerPage = 10, int $totalItemsCount = 10)
    {
        if ($activePage < 1) throw new \InvalidArgumentException('Active page must be greater than 0.');
        if ($itemsPerPage < 1) throw new \InvalidArgumentException('Items per page must be greater than 0.');
        if ($totalItemsCount < 0) throw new \InvalidArgumentException('Total items count cannot be negative.');

        $baseUrl = strtok($baseUrl, '?');
        $query = $_GET;
        $pagesShownUpDown = 5;
        $maxPage = (int) ceil($totalItemsCount / $itemsPerPage);
        $this->totalPages = $maxPage;

        // Clamp activePage to maxPage if overshoot
        $activePage = min($activePage, $maxPage);

        $firstPage = $activePage - $pagesShownUpDown;
        $lastPage = $activePage + $pagesShownUpDown;

        $offsetLow = 0 - $firstPage;
        $offsetHigh = $maxPage - $lastPage;

        if ($offsetLow > 0) {
            $firstPage += $offsetLow;
            $lastPage += $offsetLow;
        }

        if ($offsetHigh < 0) {
            $firstPage += $offsetHigh;
            $lastPage += $offsetHigh;
        }

        // Safety clamps
        $firstPage = max(0, $firstPage);
        $lastPage = min($maxPage, $lastPage);

        $pager = $maxPage > 0
            ? range($firstPage, $lastPage - 1)
            : [];

        $pagerItems = [];
        foreach ($pager as $key => $page) {
            $pageNumber = $key + 1;
            $url = $this->resolveQueryUrl($baseUrl, $query, $pageNumber, $itemsPerPage);

            $pagerItems[] = [
                "page_number" => $pageNumber,
                "url" => $url,
                "is_active" => ($key === $activePage - 1) ? 1 : 0,
            ];
        }

        $this->pagerItems = $pagerItems;

        // First page
        if ($maxPage > 0) {
            $this->first = [
                "page_number" => 1,
                "url" => $this->resolveQueryUrl($baseUrl, $query, 1, $itemsPerPage),
            ];

            // Last page
            $this->last = [
                "page_number" => $maxPage,
                "url" => $this->resolveQueryUrl($baseUrl, $query, $maxPage, $itemsPerPage),
            ];
        }

        // Previous page
        if ($activePage > 1) {
            $this->previous = [
                "page_number" => $activePage - 1,
                "url" => $this->resolveQueryUrl($baseUrl, $query, $activePage - 1, $itemsPerPage),
            ];
        }

        // Next page
        if ($activePage < $maxPage) {
            $this->next = [
                "page_number" => $activePage + 1,
                "url" => $this->resolveQueryUrl($baseUrl, $query, $activePage + 1, $itemsPerPage)
            ];
        }

        // Current page
        $this->page = [
            "page_number" => $activePage,
            "url" => $this->resolveQueryUrl($baseUrl, $query, $activePage, $itemsPerPage),
        ];
    }

    /**
     * Resolve pagination URL while preserving existing query parameters.
     */
    private function resolveQueryUrl(string $baseUrl, array $query, int $page, int $itemsPerPage): string {
        $query['page'] = $page;
        $query['display_records'] = $itemsPerPage;
        return $baseUrl . '?' . http_build_query($query);
    }
}
