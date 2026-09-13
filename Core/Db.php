<?php
declare(strict_types=1);

namespace Core;

use PDO;
use PDOStatement;
use InvalidArgumentException;

/**
 * Enum representing supported database drivers.
 */
enum DbDriver: string
{
    case MYSQL = 'mysql';
    case PGSQL = 'pgsql';
}

/**
 * PDO-based database connector and query executor.
 */
final class Db
{
    /**
     * Default ports for supported database drivers.
     */
    private const DEFAULT_PORTS = [
        'mysql' => 3306,
        'pgsql' => 5432,
    ];

    /**
     * PDO instance used for database interactions.
     */
    private ?PDO $pdo = null;

    /**
     * Constructor.
     *
     * @param string   $driver     Supported database driver ('mysql', 'pgsql').
     * @param string   $hostname   Database host.
     * @param string   $dbName     Database name.
     * @param string   $dbUser     Database username.
     * @param string   $dbPassword Database password.
     * @param int|null $port       Optional database port.
     */
    public function __construct(
        private string $driver,
        private string $hostname,
        private string $dbName,
        private string $dbUser,
        private string $dbPassword,
        private ?int $port = null
    ) {}

    /**
     * Returns the PDO connection.
     *
     * The connection is created lazily on first access.
     */
    public function getConnection(): PDO
    {
        if ($this->pdo !== null) {
            return $this->pdo;
        }

        $driver = match (strtolower(trim($this->driver))) {
            'mysql' => DbDriver::MYSQL,
            'pgsql' => DbDriver::PGSQL,
            default => throw new InvalidArgumentException(
                "Unsupported database driver: {$this->driver}. " .
                "Supported drivers are: mysql, pgsql."
            ),
        };

        $port = $this->port ?? self::DEFAULT_PORTS[$driver->value];

        $dsn = match ($driver) {
            DbDriver::MYSQL =>
                "mysql:host={$this->hostname};" .
                "port={$port};" .
                "dbname={$this->dbName};" .
                "charset=utf8mb4",

            DbDriver::PGSQL =>
                "pgsql:host={$this->hostname};" .
                "port={$port};" .
                "dbname={$this->dbName}",
        };

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        $this->pdo = new PDO(
            $dsn,
            $this->dbUser,
            $this->dbPassword,
            $options
        );

        return $this->pdo;
    }

    /**
     * Prepare and execute a query.
     *
     * Bindings must use the structure produced by QueryBuilder:
     *
     * [
     *     ':id' => [
     *         'value' => 42,
     *         'type'  => PDO::PARAM_INT,
     *     ],
     * ]
     *
     * @param string $query
     * @param array<string, array{value: mixed, type: int}> $bindings
     */
    public function execute(
        string $query,
        array $bindings = []
    ): PDOStatement {
        $statement = $this->getConnection()->prepare($query);

        foreach ($bindings as $parameter => $binding) {
            $statement->bindValue(
                $parameter,
                $binding['value'],
                $binding['type']
            );
        }

        $statement->execute();

        return $statement;
    }

    /**
     * Execute a query and return all rows.
     *
     * @param string $query
     * @param array<string, array{value: mixed, type: int}> $bindings
     * @return array<int, array<string, mixed>>
     */
    public function getAll(
        string $query,
        array $bindings = []
    ): array {
        return $this
            ->execute($query, $bindings)
            ->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Execute a query and return the first row.
     *
     * Returns null when no rows are found.
     *
     * @param string $query
     * @param array<string, array{value: mixed, type: int}> $bindings
     * @return array<string, mixed>|null
     */
    public function first(
        string $query,
        array $bindings = []
    ): ?array {
        $row = $this
            ->execute($query, $bindings)
            ->fetch(PDO::FETCH_ASSOC);

        return $row === false ? null : $row;
    }

    /**
     * Execute a query and return the first column of the first row.
     *
     * Returns null when no value is found.
     *
     * @param string $query
     * @param array<string, array{value: mixed, type: int}> $bindings
     */
    public function value(
        string $query,
        array $bindings = []
    ): mixed {
        $value = $this
            ->execute($query, $bindings)
            ->fetchColumn();

        return $value === false ? null : $value;
    }

    /**
     * Execute a query and determine whether at least one row exists.
     *
     * @param string $query
     * @param array<string, array{value: mixed, type: int}> $bindings
     */
    public function exists(
        string $query,
        array $bindings = []
    ): bool {
        return $this
            ->execute($query, $bindings)
            ->fetchColumn() !== false;
    }
}
