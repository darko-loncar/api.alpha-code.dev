<?php
declare(strict_types=1);

namespace Crystal\Tests;

use Crystal\Components\Pager;
use PHPUnit\Framework\TestCase;

final class PagerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $_GET = [];
    }

    protected function tearDown(): void
    {
        $_GET = [];

        parent::tearDown();
    }

    public function testItCreatesPagerItems(): void
    {
        $pager = new Pager('/users', 1, 10, 100);

        $this->assertCount(10, $pager->pagerItems);

        $this->assertSame(1, $pager->pagerItems[0]['page_number']);
        $this->assertTrue((bool) $pager->pagerItems[0]['is_active']);
        $this->assertSame(
            '/users?page=1&display_records=10',
            $pager->pagerItems[0]['url']
        );
    }

    public function testItMarksActivePage(): void
    {
        $pager = new Pager('/users', 5, 10, 100);

        $this->assertFalse((bool) $pager->pagerItems[3]['is_active']);
        $this->assertTrue((bool) $pager->pagerItems[4]['is_active']);
        $this->assertFalse((bool) $pager->pagerItems[5]['is_active']);
    }

    public function testItCreatesFirstPage(): void
    {
        $pager = new Pager('/users', 5, 10, 100);

        $this->assertSame(1, $pager->first['page_number']);
        $this->assertSame(
            '/users?page=1&display_records=10',
            $pager->first['url']
        );
    }

    public function testItCreatesLastPage(): void
    {
        $pager = new Pager('/users', 5, 10, 100);

        $this->assertSame(10, $pager->last['page_number']);
        $this->assertSame(
            '/users?page=10&display_records=10',
            $pager->last['url']
        );
    }

    public function testItCreatesPreviousPage(): void
    {
        $pager = new Pager('/users', 5, 10, 100);

        $this->assertSame(4, $pager->previous['page_number']);
        $this->assertSame(
            '/users?page=4&display_records=10',
            $pager->previous['url']
        );
    }

    public function testItCreatesNextPage(): void
    {
        $pager = new Pager('/users', 5, 10, 100);

        $this->assertSame(6, $pager->next['page_number']);
        $this->assertSame(
            '/users?page=6&display_records=10',
            $pager->next['url']
        );
    }

    public function testItCreatesCurrentPage(): void
    {
        $pager = new Pager('/users', 5, 10, 100);

        $this->assertSame(5, $pager->page['page_number']);
        $this->assertSame(
            '/users?page=5&display_records=10',
            $pager->page['url']
        );
    }

    public function testItDoesNotCreatePreviousPageOnFirstPage(): void
    {
        $pager = new Pager('/users', 1, 10, 100);

        $this->assertSame([], $pager->previous);
    }

    public function testItDoesNotCreateNextPageOnLastPage(): void
    {
        $pager = new Pager('/users', 10, 10, 100);

        $this->assertSame([], $pager->next);
    }

    public function testItPreservesExistingQueryParameters(): void
    {
        $_GET = [
            'search' => 'john',
            'status' => 'active',
        ];

        $pager = new Pager('/users', 2, 10, 100);

        $this->assertSame(
            '/users?search=john&status=active&page=2&display_records=10',
            $pager->page['url']
        );
    }

    public function testItUsesProvidedBaseUrl(): void
    {
        $pager = new Pager('/admin/users', 2, 10, 100);

        $this->assertSame(
            '/admin/users?page=2&display_records=10',
            $pager->page['url']
        );
    }

    public function testItLimitsPagerItemsToAvailablePages(): void
    {
        $pager = new Pager('/users', 1, 10, 25);

        $this->assertCount(3, $pager->pagerItems);

        $this->assertSame(1, $pager->pagerItems[0]['page_number']);
        $this->assertSame(2, $pager->pagerItems[1]['page_number']);
        $this->assertSame(3, $pager->pagerItems[2]['page_number']);
    }

    public function testItHandlesEmptyResultSet(): void
    {
        $pager = new Pager('/users', 1, 10, 0);

        $this->assertSame([], $pager->pagerItems);
        $this->assertSame([], $pager->first);
        $this->assertSame([], $pager->last);
        $this->assertSame([], $pager->previous);
        $this->assertSame([], $pager->next);

        $this->assertSame(0, $pager->page['page_number']);
    }
}
