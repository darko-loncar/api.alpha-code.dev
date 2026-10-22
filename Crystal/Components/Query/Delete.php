<?php
declare(strict_types=1);
namespace Crystal\Components\Query;

/**
 * Delete statement.
 */
final class Delete extends Statement
{
    public function __construct(string $table)
    {
        $this->table = $table;
    }

    public function getQuery(): string
    {
        $sql = "DELETE FROM `{$this->table}`";

        if ($this->where !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $this->where);
        }

        return $sql;
    }
}
