<?php
declare(strict_types=1);
namespace Crystal\Components;
use InvalidArgumentException;

/**
 * Immutable pagination result.
 *
 * Calculates the visible page range and navigation links for a paginated
 * collection while preserving the supplied query parameters.
 */
final readonly class Pager
{
    public array $pages;        // @var list<array{ page_number: int, url: string, is_active: bool}>
    public ?string $first;      // URL of the first page, or null when there are no pages.
    public ?string $last;       // URL of the last page, or null when there are no pages.
    public ?string $previous;   // URL of the previous page, or null when on the first page.
    public ?string $next;       // URL of the next page, or null when on the last page.

    /**
     * Create a pagination result.
     *
     * @param int $activePage       Current page number.
     * @param int $perPage          Number of items displayed per page.
     * @param int $recordCount      Total number of items being paginated.
     * @param int $pageRadius       Number of page links shown on either side of the active page.
     * @param string $requestUri    Base URL used for pagination links.
     *
     * @throws InvalidArgumentException
     */
    public function __construct(int $activePage, int $perPage = 10, int $recordCount = 0, int $pageRadius = 5, string $requestUri = '/')
    {
        if ($activePage < 1)    throw new InvalidArgumentException('Active page must be greater than 0.');
        if ($perPage < 1)       throw new InvalidArgumentException('Items per page must be greater than 0.');
        if ($recordCount < 0)   throw new InvalidArgumentException('Total items count cannot be negative.');
        if ($pageRadius < 0)    throw new InvalidArgumentException('Page radius cannot be negative.');

        // Calculate max page
        $maxPage = (int) ceil($recordCount / $perPage);

        // A requested page beyond the last page resolves to the last page.
        if ($activePage > $maxPage) throw new InvalidArgumentException('Requested non-existing page.');

        // Process base URL and query variables into array.
        $urlParts = parse_url($requestUri);
        $baseUrl = $urlParts['path'] ?? '/';
        $queryArr = [];
        parse_str($urlParts['query'] ?? '', $queryArr);

        // Calculate visible page window
        $offsetLow = min($activePage - $pageRadius, 1) - 1;
        $offsetHigh = max($activePage + $pageRadius - $maxPage, 0);

        $firstPage = max($activePage - $pageRadius - $offsetHigh, 1);
        $lastPage = min($activePage + $pageRadius - $offsetLow, $maxPage);

        // Generate page array
        $pages = [];
        for($page = $firstPage; $page <= $lastPage; ++$page) {
            $pages[$page] = [
                'url' => self::makeUrl($baseUrl, $queryArr, $page),
                'active' => $page === $activePage,
            ];
        }

        // Assign the pages.
        $this->pages = $pages;

        // First and last page links are unavailable when there are no pages.
        if ($maxPage > 0) {
            $this->first = self::makeUrl($baseUrl, $queryArr, 1);
            $this->last = self::makeUrl($baseUrl, $queryArr, $maxPage);
        } else {
            $this->first = null;
            $this->last = null;
        }

        $this->previous = ($activePage > 1)
            ? self::makeUrl($baseUrl, $queryArr, $activePage - 1)
            : null;

        $this->next = ($activePage < $maxPage)
            ? self::makeUrl($baseUrl, $queryArr, $activePage + 1)
            : null;
    }

    /**
     * Build a pagination URL while preserving existing query parameters.
     * The supplied page number replaces any existing "page" parameter.
     */
    private static function makeUrl(string $baseUrl, array $query, int $page): string
    {
        $query["page"] = $page;
        return $baseUrl . '?' . http_build_query($query);
    }
}
