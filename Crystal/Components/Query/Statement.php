<?php
declare(strict_types=1);
namespace Crystal\Components\Query;

/**
 * Statement
 */
abstract class Statement
{
    // Query bindings
    protected array $bindings = [];

    // Base table for the query
    protected string $table;

    // Columns
    protected array $columns = [];

    // WHERE clause fragments
    protected array $where = [];

    // JOIN clause fragments
    protected array $joins = [];

    // ORDER BY clause fragments
    protected array $orderBy = [];

    // Limit
    protected ?int $limit = null;

    // Offset
    protected ?int $offset = null;

    /**
     * Add a WHERE condition.
     *
     * @param string $column
     * @param mixed $value
     * @return $this
     */
    public function where(string $column, mixed $value): static
    {
        $this->bindings[] = $value;
        $this->where[] = "{$column} = ?";
        return $this;
    }

    /**
     * Add a WHERE IN condition.
     *
     * @param string $column
     * @param array<int, mixed> $values
     * @return $this
     */
    public function whereIn(string $column, array $values): static
    {
        if ($values === []) {
            // An empty IN () clause is invalid SQL. A future implementation could instead add a condition that can never be true, such as "1 = 0".
            return $this;
        }

        $placeholders = "";
        foreach ($values as $value) {
            $this->bindings[] = $value;
            $placeholders .= "?, ";
        }

        $this->where[] = sprintf('%s IN (%s)', $column, rtrim($placeholders, ", "));

        return $this;
    }

    /**
     * Add ORDER BY clause.
     *
     * @param string $column
     * @param string $direction
     * @return $this
     */
    public function orderBy(string $column, string $direction = "ASC"): static
    {
        $direction = strtoupper($direction);
        if ($direction !== 'ASC' && $direction !== 'DESC') {
            throw new \InvalidArgumentException('Order direction must be ASC or DESC.');
            }

        $this->orderBy[] = "{$column} {$direction}";

        return $this;
    }

    /**
     * Limit and optionally offset results.
     *
     * LIMIT and OFFSET are SQL values, so they are represented as
     * bindings just like WHERE values.
     *
     * @param int $limit
     * @param int|null $offset
     * @return $this
     */
    public function limit(int $limit, ?int $offset = null): static
    {
        if(isset($this->limit)) throw new \LogicException('Query limit has already been set.');

        $this->bindings[] = $limit;
        $this->limit = $limit;

        if ($offset !== null) {
            $this->bindings[] = $offset;
            $this->offset = $offset;
        }

        return $this;
    }

    /**
     * Limit and optionally offset results.
     *
     * LIMIT and OFFSET are SQL values, so they are represented as
     * bindings just like WHERE values.
     *
     * @param int $limit
     * @param int|null $offset
     * @return $this
     */
    public function page(int $page, ?int $itemsPerPage = null): static
    {
        if(isset($this->limit)) throw new \LogicException('Query limit has already been set.');

        $offset = ($page - 1) * $itemsPerPage;

        $this->bindings[] = $itemsPerPage;
        $this->limit = $itemsPerPage;

        $this->bindings[] = $offset;
        $this->offset = $offset;

        return $this;
    }


    /**
     * Add a LEFT JOIN with optional field selection.
     * Selected fields are automatically aliased for nested hydration.
     *
     * @param string $table
     * @param string $localKey
     * @param string $foreignKey
     * @param array<int, string> $fields
     * @return $this
     */
    public function leftJoin(string $table, string $localKey, string $foreignKey = 'id', array $fields = []): static
    {
        $this->joins[] =
            "LEFT JOIN `{$table}` AS $table ON " .
            "{$this->table}.{$localKey} = {$table}.{$foreignKey}";

        $this->columns[$table] = array_fill_keys($fields, "");

        return $this;
    }

    /**
     * Return bindings array
     * @return array
     */
    public function getBindings() : array
    {
        return $this->bindings;
    }

    /**
     * Process column name, prefix with table name, add alias and sql ticks
     * @param string $table
     * @param string $column
     * @param mixed $alias
     * @return string
     */
    protected function normalizeColumnName(string $table, string $column, bool $alias = false) : string
    {
        $qualifiedColumn = str_contains($column, '.')
            ? "`{$column}`"
            : "`{$table}`.`{$column}`";

        if ($alias) $qualifiedColumn .= " AS {$table}__{$column}";
        return $qualifiedColumn;
    }
}
