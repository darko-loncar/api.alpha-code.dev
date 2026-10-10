<?php
declare(strict_types=1);
namespace Crystal\Components;
use PDO;
use PDOStatement;
use LogicException;
use RuntimeException;

/**
 * PDO-based database connector
 */
abstract class Db
{
    const TABLE_VERSIONS_CACHE_FILE = __DIR__ . '/../Cache/.tableVersions';
    const TABLE_RESULT_CACHE_DIR = __DIR__ . '/../Cache/';

    private ?PDO $pdo = null;
    private string $hostname;
    private string $dbName;
    private string $dbUser;
    private string $dbPassword;
    private int $port = 3306;

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
    private function connect()
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
                PDO::ATTR_PERSISTENT => true,
            ]
        );

        return $this->pdo;
    }

    private function generateCacheKey(string $query, array $bindings) : string
    {
        $tableVersions = $this->getTableVersions();
        $queryTables = $this->parseTables($query);

        $table_key_fragment = "";

        foreach($queryTables as $table) {
            if(isset($tableVersions[$table])){
                $table_key_fragment .= $table.$tableVersions[$table];
            } else {
                $table_key_fragment .= $table."0";
            }
        }
        return $table_key_fragment."_".md5($query.serialize($bindings));
    }

    /**
     * Execute generic query
     */
    public function execute(string $query, array $bindings = []): PDOStatement
    {
        $stmt = $this->getConn()->prepare($query);
        $stmt->execute($bindings);

        if ($this->isMutableQuery($query)) {
            $this->updateTableVersions($query);
        }

        return $stmt;
    }

    /**
     * Execute a query and return all rows.
     */
    public function getAll(string $query, array $bindings = []): array
    {
        // If it is mutable query just do simple execute and fetch
        if($this->isMutableQuery($query)){
            throw new LogicException('Cannot use getAll() on mutable query. Use execute() for INSERT, UPDATE or DELETE.');
        }

        // Generate search cache key for this particular query and current table versions
        $cacheKey = $this->generateCacheKey($query, $bindings);

        // If there is existing cache return its data
        if($this->hasValidCache($cacheKey))
            return $this->getCache($cacheKey);

        // ...or query database
        $stmt = $this->getConn()->prepare($query);
        $stmt->execute($bindings);
        $results = $stmt->fetchAll();

        // Save results to cache
        file_put_contents(static::TABLE_RESULT_CACHE_DIR.$cacheKey, serialize($results), LOCK_EX); // save cache file
        return $results;
    }

    /**
     * Execute a query and return the first value of row.
     * Returns null when no rows are found.
     *
     * @return mixed|null
     */
    public function firstValue(string $query, array $bindings = []): mixed
    {
        // If it is mutable query just do simple execute and fetch
        if($this->isMutableQuery($query)){
            throw new LogicException('Cannot use getAll() on mutable query. Use execute() for INSERT, UPDATE or DELETE.');
        }

        // Generate search cache key for this particular query and current table versions
        $cacheKey = $this->generateCacheKey($query, $bindings);

        // If there is existing cache return its data
        if($this->hasValidCache($cacheKey))
            return $this->getCache($cacheKey);

        // ...or query database
        $stmt = $this->getConn()->prepare($query);
        $stmt->execute($bindings);
        $value = $stmt->fetchColumn();
        $result = $value === false ? null : $value;

        // Save results to cache
        file_put_contents(static::TABLE_RESULT_CACHE_DIR.$cacheKey, serialize($result), LOCK_EX); // save cache file
        return $result;
    }

    /**
     * Execute a query and return the first row.
     * Returns null when no rows are found.
     *
     * @return array|null
     */
    public function first(string $query, array $bindings = []): ?array
    {
        // If it is mutable query just do simple execute and fetch
        if($this->isMutableQuery($query)){
            throw new LogicException('Cannot use getAll() on mutable query. Use execute() for INSERT, UPDATE or DELETE.');
        }

        // Generate search cache key for this particular query and current table versions
        $cacheKey = $this->generateCacheKey($query, $bindings);

        // If there is existing cache return its data
        if($this->hasValidCache($cacheKey))
            return $this->getCache($cacheKey);

        // ...or query database
        $stmt = $this->getConn()->prepare($query);
        $stmt->execute($bindings);
        $row = $stmt->fetch();
        $result = $row === false ? null : $row;

        // Save results to cache
        file_put_contents(static::TABLE_RESULT_CACHE_DIR.$cacheKey, serialize($row), LOCK_EX); // save cache file
        return $row;
    }

    /**
     * Checks if cache file for given key is present
     */
    private function hasValidCache(string $cacheKey){
        $cacheFile = static::TABLE_RESULT_CACHE_DIR . $cacheKey;
        if (!is_file($cacheFile)) return false;  // If cache file is not found return exit
        if (time() - filemtime($cacheFile) >= 3600 * 24) return false; // If cache file is old
        return true;
    }

    /**
     * Searches for result cache file using key and returns data if found
     * @param string $cacheKey
     * @return null|mixed
     */
    private function getCache(string $cacheKey) : mixed
    {
        $cacheFile = static::TABLE_RESULT_CACHE_DIR . $cacheKey;
        $data = file_get_contents($cacheFile);
        if ($data === false) throw new RuntimeException('Unable to read cache file.');
        return unserialize($data, ['allowed_classes' => false]);
    }

    /**
     * Get query table dependencies by parsing query
     * @param string $query
     * @return array
     */
    private function parseTables(string $query) : array
    {
        preg_match_all('/\b(?:FROM|JOIN|UPDATE|INSERT\s+INTO|DELETE\s+FROM)\s+`?([a-zA-Z_][a-zA-Z0-9_]*)`?/i', $query, $matches);
        return array_values(array_unique($matches[1]));
    }

    /**
     * Gets array of current table versions in tracker file
     * @return array
     */
    private function getTableVersions() : array
    {
        if (!is_file(static::TABLE_VERSIONS_CACHE_FILE)) return [];
        $data = file_get_contents(static::TABLE_VERSIONS_CACHE_FILE);
        if ($data === false) return [];
        return json_decode($data, true) ?: [];
    }

    /**
     * Checks if query is of mutable type (INSERT, UPDATE, DELETE)
     * @param string $query
     * @return bool
     */
    private function isMutableQuery(string $query): bool
    {
        $type = strtoupper(strtok(ltrim($query), " \t\n\r"));
        return $type === 'INSERT' || $type === 'UPDATE' || $type === 'DELETE';
    }

    private function updateTableVersions(string $query)
    {
        $tables = $this->parseTables($query);
        $versions = $this->getTableVersions();

        // Loop through query tables and compare them to versions file
        foreach($tables as $table){
            if (isset($versions[$table])){
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


}
