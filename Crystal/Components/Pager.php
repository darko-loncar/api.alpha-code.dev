<?php
declare(strict_types=1);

namespace Crystal\Components;

/**
 * Immutable pagination result.
 *
 * Calculates the visible page range and navigation links for a paginated
 * collection while preserving the supplied query parameters.
 */
final readonly class Pager
{
    /**
     * @var list<array{
     *     page_number: int,
     *     url: string,
     *     is_active: bool
     * }>
     */
    public array $pagerItems;

    /** URL of the first page, or null when there are no pages. */
    public ?string $first;

    /** URL of the last page, or null when there are no pages. */
    public ?string $last;

    /** URL of the previous page, or null when on the first page. */
    public ?string $previous;

    /** URL of the next page, or null when on the last page. */
    public ?string $next;

    /**
     * Create a pagination result.
     *
     * @param int $activePage               Current page number.
     * @param int $itemsPerPage             Number of items displayed per page.
     * @param int $totalItemsCount          Total number of items being paginated.
     * @param int $pagesShownUpDown         Number of page links shown on either side of the active page.
     * @param string $requestUri            Base URL used for pagination links.
     *
     * @throws \InvalidArgumentException If any pagination input value is invalid.
     */
    public function __construct(
        int $activePage,
        int $itemsPerPage,
        int $totalItemsCount = 0,
        int $pagesShownUpDown = 5,
        string $requestUri = '/',
    ) {
        if ($activePage < 1) {
            throw new \InvalidArgumentException(
                'Active page must be greater than 0.'
            );
        }

        if ($itemsPerPage < 1) {
            throw new \InvalidArgumentException(
                'Items per page must be greater than 0.'
            );
        }

        if ($totalItemsCount < 0) {
            throw new \InvalidArgumentException(
                'Total items count cannot be negative.'
            );
        }

        if ($pagesShownUpDown < 0) {
            throw new \InvalidArgumentException(
                'Pages shown up/down cannot be negative.'
            );
        }

        // Remove any existing query string from the base URL. Query
        // parameters are supplied separately through $query.
        $baseUrl = strtok($requestUri, '?');
        $queryString = parse_url($requestUri, PHP_URL_QUERY);
        $query = [];
        if($queryString) parse_str($queryString, $query);

        $maxPage = (int) ceil($totalItemsCount / $itemsPerPage);

        // A requested page beyond the last page resolves to the last page.
        $activePage = min($maxPage, $activePage);

        // Start with the requested number of pages around the active page.
        $firstPage = $activePage - $pagesShownUpDown;
        $lastPage = $activePage + $pagesShownUpDown;

        // Shift the range right when it extends below page 1.
        $offsetLow = 0 - $firstPage;
        if ($offsetLow > 0) {
            $firstPage += $offsetLow;
            $lastPage += $offsetLow;
        }

        // Shift the range left when it extends beyond the last page.
        $offsetHigh = $maxPage - $lastPage;
        if ($offsetHigh < 0) {
            $firstPage += $offsetHigh;
            $lastPage += $offsetHigh;
        }

        // Keep the calculated range within valid page boundaries.
        $firstPage = max(1, $firstPage);
        $lastPage = min($maxPage, $lastPage);

        $pager = $maxPage > 0
            ? range($firstPage, $lastPage)
            : [];

        /** @var list<array{
         *     page_number: int,
         *     url: string,
         *     is_active: bool
         * }> $pagerItems
         */
        $pagerItems = [];

        foreach ($pager as $page) {
            $pagerItems[] = [
                'page_number' => $page,
                'url' => $this->resolveQueryUrl($baseUrl, $query, $page),
                'is_active' => $page === $activePage,
            ];
        }

        // First and last page links are unavailable when there are no pages.
        $first = null;
        $last = null;

        if ($maxPage > 0) {
            $first = $this->resolveQueryUrl($baseUrl, $query, 1);
            $last = $this->resolveQueryUrl($baseUrl, $query, $maxPage);
        }

        $previous = ($activePage > 1)
            ? $this->resolveQueryUrl($baseUrl, $query, $activePage - 1)
            : null;

        $next = ($activePage < $maxPage)
            ? $this->resolveQueryUrl($baseUrl, $query, $activePage + 1)
            : null;

        // Assign the fully calculated state only after all pagination
        // values have been determined.
        $this->pagerItems = $pagerItems;
        $this->first = $first;
        $this->last = $last;
        $this->previous = $previous;
        $this->next = $next;
    }

    /**
     * Build a pagination URL while preserving existing query parameters.
     *
     * The supplied page number replaces any existing "page" parameter.
     */
    private function resolveQueryUrl(
        string $baseUrl,
        array $query,
        int $page,
    ): string {
        $params = [...$query, 'page' => $page];

        return $baseUrl . '?' . http_build_query($params);
    }
}
