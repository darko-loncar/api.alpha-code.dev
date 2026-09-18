<?php
declare(strict_types=1);
namespace Crystal\Components\Query;
use Crystal\Components\Query\Query;

/**
 * Select statement.
 */
final class Select extends Query implements iStatement
{

    /**
     * Constructor
     */
    public function __construct(string $table, callable|string ...$columns)
    {
        $this->table = $table;
        $this->baseColumns = $columns;
    }

    /**
     * Build SQL query string.
     */
    public function getQuery(): string
    {
        // Base columns
        $columns = [];

        // Normalize base columns
        if(empty($this->baseColumns)) {
            $columns[] = "`{$this->table}`.*";
        } else {
            foreach($this->baseColumns as $column) {
                $columns[] = $this->normalizeColumnName($this->table, $column);
            }
        }

        // Loop through joined tables and add their columns
        foreach($this->joinedColumns as $table => $joinedColumns){
            foreach($joinedColumns as $column) {
                $columns[] = $this->normalizeColumnName($table, $column, true);
            }
        }

        $columnsSqlFragment = implode(", ", $columns);

        $sql = "SELECT {$columnsSqlFragment} FROM `{$this->table}`";

        if ($this->joins !== []) {
            $sql .= ' ' . implode(' ', $this->joins);
        }

        if ($this->where !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $this->where);
        }

        if ($this->orderBy !== []) {
            $sql .= ' ORDER BY ' . implode(', ', $this->orderBy);
        }

        if ($this->limit !== null) {
            $sql .= ' ' . $this->limit;
        }

        if ($this->offset !== null) {
            $sql .= ' ' . $this->offset;
        }

        return $sql;
    }

}
