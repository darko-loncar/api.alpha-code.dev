<?php
declare(strict_types=1);
namespace Crystal\Components\Query;
use Crystal\Components\Query\Statement;

/**
 * Select statement.
 */
final class Insert extends Statement
{
    /**
     * Constructor
     */
    public function __construct(string $table)
    {
        $this->table = $table;
    }

    public function set(string $field, mixed $value) : static
    {
        $this->columns[$this->table][$field] = "";
        $this->bindings[] = $value;
        return $this;
    }

    /**
     * Build SQL query string.
     */
    public function getQuery(): string
    {
        // Compile columns SQL fragment
        $columnSql = [];

        // Loop through tables
        foreach($this->columns[$this->table] as $columnName => $column){
            $columnSql[] = $columnName;
        }

        $placeholdersSqlFragment = implode(', ', array_fill(0, count($this->columns[$this->table]), '?'));
        $columnSqlFragment = implode(", ", $columnSql);
        $sql = "INSERT INTO `{$this->table}` ({$columnSqlFragment}) VALUES ({$placeholdersSqlFragment})";

        return $sql;
    }
}
