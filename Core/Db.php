<?php
declare(strict_types=1);
namespace Core;
use PDO;

/**
 * Enum representing supported database drivers. 
 */
enum DbDriver: string {
    case MYSQL = 'mysql';
    case PGSQL = 'pgsql';
}

/**
 * Class Db
 * 
 * PDO-based database connector. 
 */
final class Db
{
    /**
     * Default ports for supported database drivers.
     * Keyed by driver string value.
     */
    private const DEFAULT_PORTS = [
        'mysql'  => 3306,   // MySQL / MariaDB
        'pgsql'  => 5432,   // PostgreSQL
    ];

    /**
     * Readonly PDO instance used for all database interactions.
     */
    private readonly PDO $pdo;

    /**
     * Constructor
     * 
     * Initializes a PDO connection based on the provided driver and connection parameters.
     * 
     * @param string $driver        Supported database driver ('mysql', 'pgsql')
     * @param string $hostname      Database host
     * @param string $dbName        Database name
     * @param string $dbUser        Database username
     * @param string $dbPassword    Database password
     * @param int|null $port        Optional. Database port; defaults to standard port for the driver
     * 
     * @throws PDOException If connection fails
     */
    public function __construct(
        string $driver, 
        string $hostname, 
        string $dbName, 
        string $dbUser, 
        string $dbPassword, 
        ?int $port = null
    ) {

        // Validate and parse driver
        $driver = 
            match (strtolower(trim($driver))) {
                'mysql'  => DbDriver::MYSQL,
                'pgsql'  => DbDriver::PGSQL,
            default => throw new \InvalidArgumentException("Unsupported database driver: {$driver}. Supported drivers are: mysql, pgsql."),
        };
        
        // Use provided port or default port for driver
        $port = $port ?? self::DEFAULT_PORTS[$driver->value];

        // Build DSN string based on driver
        $dsn = match ($driver) {
            DbDriver::MYSQL     => "mysql:host=$hostname;port=$port;dbname=$dbName;charset=utf8mb4",
            DbDriver::PGSQL     => "pgsql:host=$hostname;port=$port;dbname=$dbName",
        };

        // PDO options
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        // Attempt connection
        $this->pdo = new PDO($dsn, $dbUser, $dbPassword, $options);
    }

    /**
     * Returns the PDO instance.
     * 
     * Controllers / models / services can use this for database queries.
     * 
     * @return PDO
     */
    public function getPdo(): PDO
    {
        return $this->pdo;
    }
}
