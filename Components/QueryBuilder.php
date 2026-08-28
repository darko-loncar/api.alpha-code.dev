<?php
declare(strict_types=1);

namespace Components;

use PDO;

/**
 * Direction used for ORDER BY clauses.
 */
enum OrderDirection: string
{
    case ASC = 'ASC';
    case DESC = 'DESC';
}

/**
 * Lightweight SQL Query Builder with:
 * - SELECT support
 * - WHERE / WHERE IN conditions
 * - JOINs with automatic aliasing
 * - ORDER BY, LIMIT, OFFSET
 * - Automatic hydration into nested arrays
 *
 * Joined columns are returned as:
 *   table__field => table[field]
 *
 * Example:
 *   outfit_hair__name => ['outfit_hair' => ['name' => ...]]
 */
final class QueryBuilder
{
    /**
     * Shared PDO connection used for all queries.
     */
    private readonly PDO $db;

    /**
     * Base table for the query.
     */
    private readonly string $table;

    /**
     * Selected columns for the query.
     */
    private array $selectColumns = [];

    /**
     * WHERE conditions as raw SQL fragments.
     */
    private array $whereConditions = [];

    /**
     * Bound query parameters for prepared statements.
     *
     * @var array<string, mixed>
     */
    private array $params = [];

    /**
     * LIMIT value for query result size.
     */
    private ?int $limit = null;

    /**
     * OFFSET value for pagination.
     */
    private ?int $offset = null;

    /**
     * ORDER BY clauses.
     *
     * @var array<int, string>
     */
    private array $orderBy = [];

    /**
     * JOIN clauses.
     *
     * @var array<int, string>
     */
    private array $joins = [];

    /**
     * List of tables involved in the query.
     *
     * @var array<int, string>
     */
    private array $tables = [];

    /**
     * Joined columns with automatic aliasing for hydration.
     *
     * Example:
     *   users.name AS users__name
     *
     * @var array<int, string>
     */
    private array $joinedColumns = [];

    /**
     * Set shared PDO connection. (To be deprecated)
     *
     * @param PDO $dbConnection Database connection instance.
     */
    public function setConnection(PDO $dbConnection): void
    {
        $this->$db = $dbConnection;
    }

    /**
     * Create a new query builder.
     *
     * @param PDO $pdo Pdo database connection.
     */
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function table(string $table): self
    {
        $this->table = $table;
        $this->tables[] = $table;
        return $this;
    }

    /**
     * Select specific columns.
     *
     * If no table prefix is provided, the base table is assumed.
     *
     * @param array<int, string> $columns Column names to select.
     * @return this
     */
    public function select(array $columns): self
    {
        $this->selectColumns = array_map(
            fn($column) =>
                strpos($column, '.') === false
                    ? "{$this->table}.{$column}"
                    : $column,
            $columns
        );

        return $this;
    }

    /**
     * Add a WHERE condition.
     *
     * @param string $column Column name.
     * @param string $operator SQL operator (=, <, >, LIKE, etc.).
     * @param mixed $value Value to bind.
     * @return this
     */
    public function where(string $column, string $operator, mixed $value): this
    {
        $paramKey = ':where_' . str_replace('.', '_', $column) . count($this->params);

        $this->whereConditions[] = "$column $operator $paramKey";
        $this->params[$paramKey] = $value;

        return $this;
    }

    /**
     * Add a WHERE IN condition.
     *
     * @param string $column Column name.
     * @param array<int, mixed> $values List of values.
     * @return this
     */
    public function whereIn(string $column, array $values): this
    {
        if (empty($values)) {
            return $this;
        }

        $placeholders = [];

        foreach ($values as $index => $value) {
            $paramKey = ":whereIn_" . str_replace('.', '_', $column) . "_$index";
            $placeholders[] = $paramKey;
            $this->params[$paramKey] = $value;
        }

        $this->whereConditions[] = "$column IN (" . implode(', ', $placeholders) . ")";

        return $this;
    }

    /**
     * Add ORDER BY clause.
     *
     * @param string $column Column name (base table assumed).
     * @param OrderDirection $orderDirection Sort direction.
     * @return this
     */
    public function orderBy(string $column, OrderDirection $orderDirection): this
    {
        $this->orderBy[] = "{$this->table}.{$column} {$orderDirection->value}";
        return $this;
    }

    /**
     * Limit and optionally offset results.
     *
     * @param int $limit Maximum number of rows.
     * @param int|null $offset Starting offset.
     * @return this
     */
    public function limit(int $limit, ?int $offset = null): this
    {
        $this->limit = $limit;
        $this->offset = $offset;

        return $this;
    }

    /**
     * Add a LEFT JOIN with optional field selection.
     *
     * Selected fields are automatically aliased for nested hydration.
     *
     * Example alias:
     *   users.name AS users__name
     *
     * @param string $table Table to join.
     * @param string $localKey Local key on base table.
     * @param string $foreignKey Foreign key on joined table.
     * @param array<int, string> $fields Fields to include from joined table.
     * @return self
     */
    public function leftJoin(
        string $table,
        string $localKey,
        string $foreignKey = 'id',
        array $fields = []
    ): this {

        $this->joins[] =
            "LEFT JOIN `$table` ON {$this->table}.{$localKey} = {$table}.{$foreignKey}";

        $this->tables[] = $table;

        foreach ($fields as $field) {
            $this->joinedColumns[] =
                "{$table}.{$field} AS {$table}__{$field}";
        }

        return $this;
    }

    /**
     * Return generated SQL string.
     *
     * @return string SQL query.
     */
    public function toSql(): string
    {
        return $this->buildSQL();
    }

    /**
     * Build SQL query string.
     *
     * @return string
     */
    private function buildSQL(): string
    {
        $columns = [];

        // Base table columns
        if (empty($this->selectColumns)) {
            $columns[] = "{$this->table}.*";
        } else {
            $columns = $this->selectColumns;
        }

        // Joined table columns
        if (!empty($this->joinedColumns)) {
            $columns = array_merge($columns, $this->joinedColumns);
        }

        $sql = "SELECT " . implode(', ', $columns) . " FROM `{$this->table}`";

        if (!empty($this->joins)) {
            $sql .= ' ' . implode(' ', $this->joins);
        }

        if (!empty($this->whereConditions)) {
            $sql .= " WHERE " . implode(" AND ", $this->whereConditions);
        }

        if (!empty($this->orderBy)) {
            $sql .= " ORDER BY " . implode(", ", $this->orderBy);
        }

        if ($this->limit !== null) {
            $sql .= " LIMIT " . $this->limit;

            if ($this->offset !== null) {
                $sql .= " OFFSET " . $this->offset;
            }
        }

        return $sql;
    }

    /**
     * Execute query and return results.
     *
     * Results are automatically hydrated into nested structure:
     * table__field => table[field]
     *
     * @return array<int, array<string, mixed>>
     */
    public function get(): array
    {
        $sql = $this->buildSQL();

        $stmt = $this->db->prepare($sql);

        foreach ($this->params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        return $this->hydrate($rows);
    }

    /**
     * Hydrate flat SQL rows into nested array structure.
     *
     * Example:
     *  users__name => users[name]
     *
     * @param array<int, array<string, mixed>> $rows Raw database rows.
     * @return array<int, array<string, mixed>>
     */
    private function hydrate(array $rows): array
    {
        $result = [];

        foreach ($rows as $row) {

            $item = [];

            foreach ($row as $key => $value) {

                if (str_contains($key, '__')) {
                    [$table, $field] = explode('__', $key, 2);
                    $item[$table][$field] = $value;
                } else {
                    $item[$key] = $value;
                }
            }

            $result[] = $item;
        }

        return $result;
    }

    /**
     * Get first result or null.
     *
     * @return array<string, mixed>|null
     */
    public function first(): ?array
    {
        $this->limit(1);
        $results = $this->get();
        return $results[0] ?? null;
    }
}