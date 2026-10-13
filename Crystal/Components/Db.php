<?php

declare(strict_types=1);

namespace Crystal\Components;

use PDO;
use RuntimeException;

/**
 * PDO/MySQL - based database connector with results cache layer
 */
abstract class Db
{
    protected const TABLE_VERSIONS_CACHE_FILE = __DIR__ . '/../Cache/.tableVersions';
    protected const TABLE_RESULT_CACHE_DIR = __DIR__ . '/../Cache/';
    protected ?PDO $pdo = null;

    /**
     * Constructor
     *
     * @param string $hostname   Database host.
     * @param string $dbName     Database name.
     * @param string $dbUser     Database username.
     * @param string $dbPassword Database password.
     * @param int $port       Optional database port. Defaults 3306.
     */
    public function __construct(
        protected string $hostname,
        protected string $dbName,
        protected string $dbUser,
        protected string $dbPassword,
        protected int $port = 3306
    ){}

    /**
     * Connects to DB
     */
    public function getConn(): PDO
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
     * Prepare query
     * @param string $dbName
     * @param string $query
     * @param array $bindings
     * @return array
     */
    private function prepare(string $host, string $dbName, string $mode, string $query, array $bindings = []): array
    {
        $cacheKey = self::generateCacheKey($host, $dbName, $mode, $query, $bindings);
        $isCacheable = preg_match('/^SELECT\s/i', ltrim($query)) === 1;

        if ($isCacheable === true && self::isValidCache($cacheKey)) {
            return [
                "isCacheable" => true,
                "cacheHit" => true,
                "cacheKey" => $cacheKey,
                "cachedResult" => static::getCache($cacheKey),
                "pdoStatement" => null
            ];
        }

        $stmt = $this->getConn()->prepare($query);
        $stmt->execute($bindings);

        if (!$isCacheable) {
            self::updateTableVersions($dbName, $query);
        }
        return [
            "isCacheable" => $isCacheable,
            "cacheHit" => false,
            "cacheKey" => $cacheKey,
            "cachedResult" => null,
            "pdoStatement" => $stmt
        ];
    }

    /**
     * Execute a query and return all rows.
     * @return array
     */
    public function getAll(string $query, array $bindings = []): array
    {
        $queryData = $this->prepare($this->hostname, $this->dbName, "getAll", $query, $bindings);
        if ($queryData["cacheHit"]) return $queryData["cachedResult"];
        $results = $queryData["pdoStatement"]->fetchAll();
        if($queryData["isCacheable"] === true) file_put_contents(static::TABLE_RESULT_CACHE_DIR . $queryData["cacheKey"], serialize($results), LOCK_EX);
        return $results;
    }

    /**
     * Execute a query and return the first value of row.
     * @return mixed|null
     */
    public function firstValue(string $query, array $bindings = []): mixed
    {
        $queryData = $this->prepare($this->hostname, $this->dbName, "firstValue", $query, $bindings);
        if ($queryData["cacheHit"]) return $queryData["cachedResult"];
        $value = $queryData["pdoStatement"]->fetchColumn();
        $result = $value === false ? null : $value;
        if($queryData["isCacheable"] === true) file_put_contents(static::TABLE_RESULT_CACHE_DIR . $queryData["cacheKey"], serialize($result), LOCK_EX);
        return $result;
    }

    /**
     * Execute a query and return the first row.
     * Returns null when no rows are found.
     * @return array|null
     */
    public function first(string $query, array $bindings = []): ?array
    {
        $queryData = $this->prepare($this->hostname, $this->dbName, "first", $query, $bindings);
        if ($queryData["cacheHit"]) return $queryData["cachedResult"];
        $value = $queryData["pdoStatement"]->fetch();
        $result = $value === false ? null : $value;
        if($queryData["isCacheable"] === true) file_put_contents(static::TABLE_RESULT_CACHE_DIR . $queryData["cacheKey"], serialize($result), LOCK_EX);
        return $result;
    }

    /**
     * Generate cache key
     * @param string $dbName
     * @param string $query,
     * @param array $bindings
     * @return string
     */
    private static function generateCacheKey(string $host, string $dbName, string $mode, string $query, array $bindings): string
    {
        $tableVersions = self::getTableVersions();
        $queryTables = self::parseTables($query);

        $tableKeyFragment = '';

        foreach ($queryTables as $table) {
            $key = $dbName . '.' . $table;
            $tableKeyFragment .= $key . ($tableVersions[$key] ?? 0);
        }

        return hash("xxh128", $mode. "." . $host . '.' . $dbName . '_' . $tableKeyFragment . '_' . $query . serialize($bindings));
    }

    /**
     * Checks if cache file for given key is present
     * @param string $cacheKey
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
        return unserialize($data, ['allowed_classes' => false]);
    }

    /**
     * Updates table versions tracker file
     * @param string $dbName
     * @param string $query
     * @return void
     */
    private static function updateTableVersions(string $dbName, string $query): void
    {
        $tables = self::parseTables($query);
        $versions = self::getTableVersions();

        // Loop through query tables and compare them to versions file
        foreach ($tables as $table) {
            if (isset($versions[$dbName.".".$table])) {
                // If it is tracked table, bump version by 1
                ++$versions[$dbName.".".$table];
            } else {
                // If it is untracked add it to list with initial version 1
                $versions[$dbName.".".$table] = 1;
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
    private static function parseTables(string $query): array
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
