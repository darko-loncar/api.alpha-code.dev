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
        // Bump table versions on INSERT, UPDATE, DELETE etc..
        $query_tables = $this->getQueryTableDependencies($query);
        if($this->isBumpableQuery($query)){
            $table_versions = $this->getTableVersions();
            foreach($query_tables as $key => $table){
                if(isset($table_versions[$table])){
                    $table_versions[$table] += 1;
                } else {
                    $table_versions[$table] = 0;;
                }
            }
            file_put_contents(__DIR__ . '/../Cache/.tableVersions', json_encode($table_versions), LOCK_EX);
        }

        $cacheKey = $this->generateCacheKey($query, $bindings);
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
        // Bump table versions on INSERT, UPDATE, DELETE etc..
        $query_tables = $this->getQueryTableDependencies($query);
        if($this->isBumpableQuery($query)){
            $table_versions = $this->getTableVersions();
            foreach($query_tables as $key => $table){
                if(isset($table_versions[$table])){
                    $table_versions[$table] += 1;
                } else {
                    $table_versions[$table] = 0;;
                }
            }
            file_put_contents(__DIR__ . '/../Cache/.tableVersions', json_encode($table_versions), LOCK_EX);
        }


        $cacheKey = $this->generateCacheKey($query, $bindings);
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
        // Bump table versions on INSERT, UPDATE, DELETE etc..
        $query_tables = $this->getQueryTableDependencies($query);
        if($this->isBumpableQuery($query)){
            $table_versions = $this->getTableVersions();
            foreach($query_tables as $key => $table){
                if(isset($table_versions[$table])){
                    $table_versions[$table] += 1;
                } else {
                    $table_versions[$table] = 0;;
                }
            }
            file_put_contents(__DIR__ . '/../Cache/.tableVersions', json_encode($table_versions), LOCK_EX);
        }

        $cacheKey = $this->generateCacheKey($query, $bindings);
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

            if ($data !== false && time() - $created < 60 * 60 * 24) {
                error_log("Reusing cache");
                return unserialize($data);
            }
        error_log("Cache stale, query db...");
        }
        return false;
    }

    private function generateCacheKey(string $query, array $bindings)
    {
        $query_tables = $this->getQueryTableDependencies($query);
        $table_versions = $this->getTableVersions();
        $table_key_fragment = "";
        foreach($query_tables as $table) {
            if(isset($table_versions[$table])){
                $table_key_fragment .= $table.$table_versions[$table];
            } else {
                $table_key_fragment .= $table."0";

                // Update table versions array with new table and set version to 0
                $table_versions[$table] = 0;
            }
        }

        // Update table tracker file
        file_put_contents(__DIR__ . '/../Cache/.tableVersions', json_encode($table_versions), LOCK_EX);

        return $table_key_fragment."_".md5($query.serialize($bindings));
    }

    private function getQueryTableDependencies(string $query)
    {
        // Get query table dependencies by parsing query
        preg_match_all('/\b(?:FROM|JOIN|UPDATE|INSERT\s+INTO|DELETE\s+FROM)\s+`?([a-zA-Z_][a-zA-Z0-9_]*)`?/i', $query, $matches);
        return array_values(array_unique($matches[1]));
    }

    private function getTableVersions()
    {
        $tableTrackerFile = __DIR__ . '/../Cache/.tableVersions';

        // Check if we have table tracker file, if not create empty
        if(!is_file($tableTrackerFile))
            file_put_contents($tableTrackerFile, '');

        // Get table versions from tracker file
        return json_decode(file_get_contents($tableTrackerFile), true) ?: [];
    }

    private function isBumpableQuery($query){
        $queryType = strtoupper(strtok(ltrim($query), " \t\n\r"));
        if($queryType == "INSERT" || $queryType == "UPDATE" || $queryType == "DELETE" ){
            return true;
        }
        return false;
    }

}
