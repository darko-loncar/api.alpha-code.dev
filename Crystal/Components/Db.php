<?php
declare(strict_types=1);
namespace Crystal\Components;
use PDO;

/**
 * PDO-based database connector
 */
abstract class Db
{
    // Default ports for supported database drivers
    private const DEFAULT_PORT = 3306;

    // PDO instance
    private ?PDO $pdo = null;

    /**
     * Constructor
     *
     * @param string   $hostname   Database host.
     * @param string   $dbName     Database name.
     * @param string   $dbUser     Database username.
     * @param string   $dbPassword Database password.
     * @param int|null $port       Optional database port.
     */
    public function __construct(
        private string $hostname,
        private string $dbName,
        private string $dbUser,
        private string $dbPassword,
        private ?int $port = null
        ) {}

    /**
     * Returns the PDO connection. The connection is created lazily on first access.
     */
    public function getConnection(): PDO
    {
        if ($this->pdo !== null) return $this->pdo;

        $port = $this->port ?? self::DEFAULT_PORT;
        $dsn = "mysql:host={$this->hostname};port={$port};dbname={$this->dbName};charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        $this->pdo = new PDO($dsn, $this->dbUser, $this->dbPassword, $options);

        return $this->pdo;
    }

    /**
     * Prepare and execute a query.
     *
     * @param string $query
     * @param array $bindings
     * @param array<string, array{value: mixed, type: int}> $bindings
     */
    protected function execute(string $query, array $bindings = []): \PDOStatement
    {
        if ($this->pdo === null) $this->getConnection();
        $statement = $this->pdo->prepare($query);
        $statement->execute($bindings);
        return $statement;
    }

    /**
     * Execute a query and return all rows.
     *
     * @param string $query
     * @param array $bindings
     * @return array<int, array<string, mixed>>
     */
    public function getAll(string $query, array $bindings = []): array
    {
        return $this
            ->execute($query, $bindings)
            ->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Execute a query and return the first row.
     * Returns null when no rows are found.
     *
     * @param string $query
     * @param array $bindings
     * @return array<string, mixed>|null
     */
    public function first(string $query, array $bindings = []): ?array {
        $row = $this
            ->execute($query, $bindings)
            ->fetch(PDO::FETCH_ASSOC);

        return $row === false ? null : $row;
    }

}
