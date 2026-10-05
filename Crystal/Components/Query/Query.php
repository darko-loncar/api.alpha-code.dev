<?php
declare(strict_types=1);
namespace Crystal\Components\Query;

/**
 * SQL Query Builder.
 */
abstract class Query
{

    /**
     * Factory method: Select statement.
     * @return Select
     */
    public static function select(string $table, array $columns = []) : Select
    {
        return new Select($table, $columns);
    }

}
