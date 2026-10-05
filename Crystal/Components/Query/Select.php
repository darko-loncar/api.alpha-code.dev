<?php
declare(strict_types=1);
namespace Crystal\Components\Query;
use Crystal\Components\Query\Statement;

/**
 * Select statement.
 */
final class Select extends Statement
{
    /**
     * Constructor
     */
    public function __construct(string $table, array $columns = [])
    {
        $this->table = $table;
        $this->columns[$table] = array_fill_keys($columns, "");
    }

    /**
     * Build SQL query string.
     */
    public function getQuery(): string
    {
        // Compile columns SQL fragment
        $columnSql = [];

        // Loop through tables
        foreach($this->columns as $table => $columns){
            if($columns === []) {
                $columnSql[] = "`{$table}`.*";
                continue;
            }

            foreach($columns as $column => $processors) {
                $columnSql[] = $this->normalizeColumnName($table, $column, true);
            }
        }

        $columnSqlFragment = implode(", ", $columnSql);
        $sql = "SELECT {$columnSqlFragment} FROM `{$this->table}`";

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
            $sql .= ' LIMIT ?';
        }

        if ($this->offset !== null) {
            $sql .= ' OFFSET ?';
        }

        return $sql;
    }

    /**
     * Build SQL query string.
     */
    public function getCountQuery(): string
    {
        $sql = "SELECT COUNT(*) FROM `{$this->table}`";

        if ($this->joins !== []) {
            $sql .= ' ' . implode(' ', $this->joins);
        }

        if ($this->where !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $this->where);
        }

        return $sql;
    }


}
