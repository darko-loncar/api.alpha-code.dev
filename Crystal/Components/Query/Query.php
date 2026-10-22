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
    public static function select(string $table, array $columns = []) : Statement
    {
        return new Select($table, $columns);
    }

    /**
     * Factory method: Insert statement.
     * @return Select
     */
    public static function insert(string $table) : Statement
    {
        return new Insert($table);
    }

    /**
     * Factory method: Update statement.
     */
    public static function update(string $table) : Statement
    {
        return new Update($table);
    }

    /**
     * Factory method: Delete statement.
     */
    public static function delete(string $table) : Statement
    {
        return new Delete($table);
    }

}
