<?php
declare(strict_types=1);
namespace Crystal\Components;
use PDO;
use PDOStatement;

/**
 * PDO-based database connector
 */
abstract class Db
{
    // PDO instance
    private ?PDO $pdo = null;

    /**
     * Constructor
     *
     * @param string   $hostname   Database host.
     * @param string   $dbName     Database name.
     * @param string   $dbUser     Database username.
     * @param string   $dbPassword Database password.
     * @param int      $port       Optional database port. Defaults 3306.
     */
    public function __construct(
        private string $hostname,
        private string $dbName,
        private string $dbUser,
        private string $dbPassword,
        private int $port = 3306
        ) {}

    /**
     * Returns the PDO connection. The connection is created lazily on first access.
     */
    public function getConnection()
    {
        if ($this->pdo !== null) return $this->pdo;

        $this->pdo = new PDO(
            "mysql:host={$this->hostname};port={$this->port};dbname={$this->dbName};charset=utf8mb4",
            $this->dbUser,
            $this->dbPassword,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                //PDO::ATTR_PERSISTENT => true,
            ]
        );

        return $this->pdo;
    }

    /**
     * Prepare and execute a query.
     *
     * @param string $query
     * @param array $bindings
     * @param array<string, array{value: mixed, type: int}> $bindings
     */
    public function execute(string $query, array $bindings = []): PDOStatement
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
        $cacheKey = md5($query.serialize($bindings));
        $cacheData = $this->queryCache($cacheKey);
        if($cacheData) return $cacheData;

        $results = $this
            ->execute($query, $bindings)
            ->fetchAll(PDO::FETCH_ASSOC);

        file_put_contents(__DIR__ . '/../Cache/'.$cacheKey, serialize($results), LOCK_EX);
        return $results;
    }

    /**
     * Execute a query and return the first row.
     * Returns null when no rows are found.
     *
     * @param string $query
     * @param array $bindings
     * @return array<string, mixed>|null
     */
    public function first(string $query, array $bindings = []): ?array
    {
        $cacheKey = md5($query.serialize($bindings));
        $cacheData = $this->queryCache($cacheKey);
        if($cacheData) return $cacheData;

        $row = $this
            ->execute($query, $bindings)
            ->fetch(PDO::FETCH_ASSOC);

        file_put_contents(__DIR__ . '/../Cache/'.$cacheKey, serialize($row), LOCK_EX);

        return $row === false ? null : $row;
    }

    /**
     * Execute a query and return the first value of row.
     * Returns null when no rows are found.
     *
     * @param string $query
     * @param array $bindings
     * @return mixed|null
     */
    public function firstValue(string $query, array $bindings = []): mixed
    {
        $cacheKey = md5($query.serialize($bindings));
        $cacheData = $this->queryCache($cacheKey);
        if($cacheData) return $cacheData;

        $row = $this
            ->execute($query, $bindings)
            ->fetchColumn();

        file_put_contents(__DIR__ . '/../Cache/'.$cacheKey, serialize($row), LOCK_EX);

        return $row === false ? null : $row;
    }

    private function queryCache(string $cacheKey)
    {
        $cacheFile = __DIR__ . '/../Cache/' . $cacheKey;

        if (is_file($cacheFile)) {
            $data = file_get_contents($cacheFile);
            $created = filemtime($cacheFile);

            if ($data !== false && time() - $created < 60 * 30) {
                error_log("Reusing cache");
                return unserialize($data);
            }
        error_log("Cache stale, query db...");
        }
        return false;
    }

}
