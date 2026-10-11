<?php

declare(strict_types=1);

namespace Crystal\Components;

use PDO;
use PDOStatement;
use RuntimeException;

/**
 * PDO-based database connector
 */
class Db
{
    private const TABLE_VERSIONS_CACHE_FILE = __DIR__ . '/../Cache/.tableVersions';
    private const TABLE_RESULT_CACHE_DIR = __DIR__ . '/../Cache/';

    protected ?PDO $pdo = null;
    protected ?PDOStatement $pdoStatement = null;
    protected ?string $cacheKey = null;
    protected bool $hasValidCache = false;
    protected string $hostname;
    protected string $dbName;
    protected string $dbUser;
    protected string $dbPassword;
    protected int $port = 3306;

    /**
     * Constructor
     *
     * @param string   $hostname   Database host.
     * @param string   $dbName     Database name.
     * @param string   $dbUser     Database username.
     * @param string   $dbPassword Database password.
     * @param int      $port       Optional database port. Defaults 3306.
     */
    public function __construct(string $hostname, string $dbName, string $dbUser, string $dbPassword, int $port = 3306)
    {
        $this->hostname = $hostname;
        $this->dbName = $dbName;
        $this->dbUser = $dbUser;
        $this->dbPassword = $dbPassword;
        $this->port = $port;
    }

    /**
     * Returns the PDO connection. The connection is created lazily on first access.
     */
    public function getConn(): PDO
    {
        return $this->pdo ?? $this->connect();
    }

    /**
     * Connects to DB
     */
    private function connect(): PDO
    {
        if ($this->pdo !== null) {
            return $this->pdo;
        }

        $this->pdo = new PDO(
            "mysql:host={$this->hostname};port={$this->port};dbname={$this->dbName};charset=utf8mb4",
            $this->dbUser,
            $this->dbPassword,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );

        return $this->pdo;
    }

    /**
     * Execute generic query
     */
    public function execute(string $query, array $bindings = []): self
    {
        // Reset state on every query execution
        $this->cacheKey = null;
        $this->hasValidCache = false;
        $this->pdoStatement = null;

        // If it is mutable query execute it directly, store prepared statement and update table tracker
        if (strtoupper(strtok(ltrim($query), " \t\n\r")) !== "SELECT") {
            $stmt = $this->getConn()->prepare($query);
            $stmt->execute($bindings);
            self::updateTableVersions($query);
            return $this;
        }

        // Generate search cache key for this particular query and current table versions
        $this->cacheKey = $this->generateCacheKey($this->dbName, $query, $bindings);

        if (isset($this->cacheKey)) {
            if (self::isValidCache($this->cacheKey)) {
                $this->hasValidCache = true;
                return $this;
            }
        }

        // In last case we need to fallback to query DB
        $stmt = $this->getConn()->prepare($query);
        $stmt->execute($bindings);
        $this->pdoStatement = $stmt;
        return $this;
    }

    /**
     * Execute a query and return all rows.
     * @return array
     */
    public function getAll(): array
    {
        if ($this->hasValidCache) {
            return self::getCache($this->cacheKey);
        }
        $results = $this->pdoStatement->fetchAll();
        file_put_contents(static::TABLE_RESULT_CACHE_DIR . $this->cacheKey, serialize($results), LOCK_EX);
        return $results;
    }

    /**
     * Execute a query and return the first value of row.
     * @return mixed|null
     */
    public function firstValue(): mixed
    {
        if ($this->hasValidCache) {
            return self::getCache($this->cacheKey);
        }
        $value = $this->pdoStatement->fetchColumn();
        $result = $value === false ? null : $value;
        file_put_contents(static::TABLE_RESULT_CACHE_DIR . $this->cacheKey, serialize($result), LOCK_EX);
        return $result;
    }

    /**
     * Execute a query and return the first row.
     * Returns null when no rows are found.
     * @return array|null
     */
    public function first(): ?array
    {
        if ($this->hasValidCache) {
            return self::getCache($this->cacheKey);
        }
        $value = $this->pdoStatement->fetch();
        $result = $value === false ? null : $value;
        file_put_contents(static::TABLE_RESULT_CACHE_DIR . $this->cacheKey, serialize($result), LOCK_EX);
        return $result;
    }

    private static function generateCacheKey(string $dbName, string $query, array $bindings): string
    {
        $tableVersions = self::getTableVersions();
        $queryTables = self::parseTables($query);

        $table_key_fragment = "";

        foreach ($queryTables as $table) {
            if (isset($tableVersions[$table])) {
                $table_key_fragment .= $table . $tableVersions[$table];
            } else {
                $table_key_fragment .= $table . "0";
            }
        }
        return $dbName . "_" . $table_key_fragment . "_" . md5($query . serialize($bindings));
    }

    /**
     * Checks if cache file for given key is present
     * @return bool
     */
    private static function isValidCache(string $cacheKey): bool
    {
        $cacheFile = static::TABLE_RESULT_CACHE_DIR . $cacheKey;
        if (!is_file($cacheFile)) {
            return false;
        }
        if (time() - filemtime($cacheFile) >= 3600 * 24) {
            unlink($cacheFile);
            return false;
        }
        return true;
    }

    /**
     * Searches for result cache file using key and returns data if found
     * @param string $cacheKey
     * @return null|mixed
     */
    private static function getCache(string $cacheKey): mixed
    {
        $cacheFile = static::TABLE_RESULT_CACHE_DIR . $cacheKey;
        $data = file_get_contents($cacheFile);
        if ($data === false) {
            throw new RuntimeException('Unable to read cache file.');
        }
        return unserialize($data);
    }

    private static function updateTableVersions(string $query)
    {
        $tables = self::parseTables($query);
        $versions = self::getTableVersions();

        // Loop through query tables and compare them to versions file
        foreach ($tables as $table) {
            if (isset($versions[$table])) {
                // If it is tracked table, bump version by 1
                ++$versions[$table];
            } else {
                // If it is untracked add it to list with initial version 1
                $versions[$table] = 1;
            }
        }

        // Update table tracker file
        file_put_contents(static::TABLE_VERSIONS_CACHE_FILE, json_encode($versions), LOCK_EX);
    }

    /**
     * Get query table dependencies by parsing query
     * @param string $query
     * @return array
     */
    private static function parseTables(string $query) : array
    {
        preg_match_all('/\b(?:FROM|JOIN|UPDATE|INSERT\s+INTO|DELETE\s+FROM)\s+`?([a-zA-Z_][a-zA-Z0-9_]*)`?/i', $query, $matches);
        return array_values(array_unique($matches[1]));
    }

    /**
     * Gets array of current table versions in tracker file
     * @return array
     */
    private static function getTableVersions(): array
    {
        if (!is_file(static::TABLE_VERSIONS_CACHE_FILE)) {
            file_put_contents(static::TABLE_VERSIONS_CACHE_FILE, '{}');
            return [];
        }
        $data = file_get_contents(static::TABLE_VERSIONS_CACHE_FILE);
        if ($data === false) {
            return [];
        }
        return json_decode($data, true) ?: [];
    }
}
