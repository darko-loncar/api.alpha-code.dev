<?php
declare(strict_types=1);

namespace Components\Query;

use PDO;
use Components\Query\Select;

/**
 * Direction used for ORDER BY clauses.
 */
enum OrderDirection: string
{
    case ASC = 'ASC';
    case DESC = 'DESC';
}

/**
 * Lightweight SQL Query Builder.
 *
 * The builder is responsible only for constructing SQL and its bindings.
 * Database execution is handled elsewhere.
 */
abstract class Query
{

    // Base table for the query
    protected string $table;

    // Joined tables
    protected array $joinedTables = [];

    // Base table columns
    protected array $baseColumns = [];

    // Joined columns
    protected array $joinedColumns = [];

    // WHERE clause fragments
    protected array $where = [];

    // JOIN clause fragments
    protected array $joins = [];

    // ORDER BY clause fragments
    protected array $orderBy = [];

    // LIMIT clause
    protected ?string $limit = null;

    // OFFSET clause
    protected ?string $offset = null;

    // Query bindings
    protected array $bindings = [];

    /**
     * Select statement columns.
     * @return $this
     */
    public static function select(string $table, array $columns = [])
    {
        return new Select($table, ...$columns);
    }

    /**
     * Add a WHERE condition.
     *
     * @param string $column
     * @param string $operator
     * @param mixed $value
     * @return $this
     */
    public function where(string $column, string $operator, mixed $value): static 
    {
        $paramKey = $this->createBinding(
            "where_" . str_replace('.', '_', $column),
            $value
        );

        $this->where[] = "{$column} {$operator} {$paramKey}";

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
            /*
             * An empty IN () clause is invalid SQL.
             *
             * A future implementation could instead add a condition
             * that can never be true, such as "1 = 0".
             */
            return $this;
        }

        $placeholders = [];

        foreach ($values as $value) {
            $placeholders[] = $this->createBinding(
                "whereIn_" . str_replace('.', '_', $column),
                $value
            );
        }

        $this->where[] = sprintf(
            '%s IN (%s)',
            $column,
            implode(', ', $placeholders)
        );

        return $this;
    }

    /**
     * Add ORDER BY clause.
     *
     * @param string $column
     * @param OrderDirection $orderDirection
     * @return $this
     */
    public function orderBy(string $column, OrderDirection $orderDirection = OrderDirection::ASC): static
    {
        $column = str_contains($column, '.')
            ? $column
            : "{$this->table}.{$column}";

        $this->orderBy[] = "{$column} {$orderDirection->value}";

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
        $limitParam = $this->createBinding('limit', $limit, PDO::PARAM_INT);
        $this->limit = "LIMIT {$limitParam}";

        if ($offset !== null) {
            $offsetParam = $this->createBinding('offset', $offset, PDO::PARAM_INT);
            $this->offset = "OFFSET {$offsetParam}";
        }

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
        $this->joinedTables[] = $table;

        $this->joins[] =
            "LEFT JOIN `{$table}` AS $table ON " .
            "{$this->table}.{$localKey} = {$table}.{$foreignKey}";

        foreach ($fields as $field) {
            $this->joinedColumns[$table][] = $field;
        }

        return $this;
    }

    public function max(){
        
    }

    /**
     * Return query bindings.
     *
     * @return array<string, array{value: mixed, type: int}>
     */
    public function getBindings(): array
    {
        return $this->bindings;
    }

    /**
     * Process array of columns, prefix them with table name, add alias and sql ticks
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

    /**
     * Create a unique binding placeholder and store its value/type.
     *
     * @return string Placeholder including the leading colon.
     */
    protected function createBinding(string $name, mixed $value, ?int $type = null): string 
    {
        $baseName = ':' . $name;
        $paramKey = $baseName;
        $counter = 0;

        while (isset($this->bindings[$paramKey])) {
            $counter++;
            $paramKey = "{$baseName}_{$counter}";
        }

        if ($type === null) {
            $type = $this->getBindingType($value);
        }

        $this->bindings[$paramKey] = [
            'value' => $value,
            'type' => $type,
        ];

        return $paramKey;
    }

    /**
     * Determine the appropriate PDO binding type.
     */
    protected function getBindingType(mixed $value): int
    {
        return match (true) {
            $value === null => PDO::PARAM_NULL,
            is_bool($value) => PDO::PARAM_BOOL,
            is_int($value) => PDO::PARAM_INT,
            default => PDO::PARAM_STR,
        };
    }

}
