<?php
declare(strict_types=1);

namespace Crystal\Tests;

use Crystal\Components\Query\Query;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class QueryTest extends TestCase
{
    public function testSelectAll(): void
    {
        $query = Query::select('user');

        $this->assertSame(
            'SELECT `user`.* FROM `user`',
            $query->getQuery()
        );

        $this->assertSame([], $query->getBindings());
    }

    public function testSelectSpecificColumns(): void
    {
        $query = Query::select(
            'user',
            ['id', 'uuid', 'email']
        );

        $this->assertSame(
            'SELECT `user`.`id` AS user__id, `user`.`uuid` AS user__uuid, `user`.`email` AS user__email FROM `user`',
            $query->getQuery()
        );

        $this->assertSame([], $query->getBindings());
    }

    public function testWhere(): void
    {
        $query = Query::select('user')
            ->where('user.is_active', true);

        $this->assertSame(
            'SELECT `user`.* FROM `user` WHERE user.is_active = ?',
            $query->getQuery()
        );

        $this->assertSame(
            [true],
            $query->getBindings()
        );
    }

    public function testMultipleWhereConditions(): void
    {
        $query = Query::select('user')
            ->where('user.is_active', true)
            ->where('user.id', 10);

        $this->assertSame(
            'SELECT `user`.* FROM `user` WHERE user.is_active = ? AND user.id = ?',
            $query->getQuery()
        );

        $this->assertSame(
            [true, 10],
            $query->getBindings()
        );
    }

    public function testWhereIn(): void
    {
        $query = Query::select('user')
            ->whereIn('user.id', [10, 20, 30]);

        $this->assertSame(
            'SELECT `user`.* FROM `user` WHERE user.id IN (?, ?, ?)',
            $query->getQuery()
        );

        $this->assertSame(
            [10, 20, 30],
            $query->getBindings()
        );
    }

    public function testEmptyWhereInDoesNothing(): void
    {
        $query = Query::select('user')
            ->whereIn('user.id', []);

        $this->assertSame(
            'SELECT `user`.* FROM `user`',
            $query->getQuery()
        );

        $this->assertSame([], $query->getBindings());
    }

    public function testOrderByDefaultsToAscending(): void
    {
        $query = Query::select('user')
            ->orderBy('user.id');

        $this->assertSame(
            'SELECT `user`.* FROM `user` ORDER BY user.id ASC',
            $query->getQuery()
        );

        $this->assertSame([], $query->getBindings());
    }

    public function testOrderByDescending(): void
    {
        $query = Query::select('user')
            ->orderBy('user.id', 'DESC');

        $this->assertSame(
            'SELECT `user`.* FROM `user` ORDER BY user.id DESC',
            $query->getQuery()
        );
    }

    public function testOrderByQualifiedColumn(): void
    {
        $query = Query::select('user')
            ->orderBy('user.created_at');

        $this->assertSame(
            'SELECT `user`.* FROM `user` ORDER BY user.created_at ASC',
            $query->getQuery()
        );
    }

    public function testInvalidOrderDirectionThrowsException(): void
    {
        $query = Query::select('user');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Order direction must be ASC or DESC.'
        );

        $query->orderBy('id', 'INVALID');
    }

    public function testLimit(): void
    {
        $query = Query::select('user')
            ->limit(25);

        $this->assertSame(
            'SELECT `user`.* FROM `user` LIMIT ?',
            $query->getQuery()
        );

        $this->assertSame(
            [25],
            $query->getBindings()
        );
    }

    public function testLimitAndOffset(): void
    {
        $query = Query::select('user')
            ->limit(25, 50);

        $this->assertSame(
            'SELECT `user`.* FROM `user` LIMIT ? OFFSET ?',
            $query->getQuery()
        );

        $this->assertSame(
            [25, 50],
            $query->getBindings()
        );
    }

    public function testLeftJoinWithoutFields(): void
    {
        $query = Query::select('user')
            ->leftJoin(
                'user_auth',
                'id',
                'id'
            );

        $this->assertSame(
            'SELECT `user`.*, `user_auth`.* FROM `user` LEFT JOIN `user_auth` AS user_auth ON user.id = user_auth.id',
            $query->getQuery()
        );

        $this->assertSame([], $query->getBindings());
    }

    public function testLeftJoinWithSelectedFields(): void
    {
        $query = Query::select('user')
            ->leftJoin(
                'user_auth',
                'id',
                'id',
                ['id', 'identifier', 'last_login_at']
            );

        $this->assertSame(
            'SELECT `user`.*, `user_auth`.`id` AS user_auth__id, `user_auth`.`identifier` AS user_auth__identifier, `user_auth`.`last_login_at` AS user_auth__last_login_at FROM `user` LEFT JOIN `user_auth` AS user_auth ON user.id = user_auth.id',
            $query->getQuery()
        );

        $this->assertSame([], $query->getBindings());
    }

    public function testCompleteUserQuery(): void
    {
        $query = Query::select('user')
            ->where('user.is_active', true)
            ->leftJoin(
                'user_auth',
                'id',
                'id',
                ['id', 'identifier', 'last_login_at']
            )
            ->orderBy('user.id')
            ->limit(25, 0);

        $this->assertSame(
            'SELECT `user`.*, `user_auth`.`id` AS user_auth__id, `user_auth`.`identifier` AS user_auth__identifier, `user_auth`.`last_login_at` AS user_auth__last_login_at FROM `user` LEFT JOIN `user_auth` AS user_auth ON user.id = user_auth.id WHERE user.is_active = ? ORDER BY user.id ASC LIMIT ? OFFSET ?',
            $query->getQuery()
        );

        $this->assertSame(
            [true, 25, 0],
            $query->getBindings()
        );
    }

    public function testBindingOrderIsPreserved(): void
    {
        $query = Query::select('user')
            ->where('user.is_active', true)
            ->whereIn('user.id', [10, 20, 30])
            ->limit(25, 50);

        $this->assertSame(
            [
                true,
                10,
                20,
                30,
                25,
                50,
            ],
            $query->getBindings()
        );
    }
}
