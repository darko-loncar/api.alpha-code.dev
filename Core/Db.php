<?php
declare(strict_types=1);

namespace Core;

use PDO;
use PDOException;

/**
 * Enum representing supported database drivers.
 * 
 * Using enum ensures type safety and avoids magic strings.
 */
enum DbDriver: string {
    case MYSQL = 'mysql';
    case PGSQL = 'pgsql';
    case SQLSRV = 'sqlsrv';
}

/**
 * Class Db
 * 
 * PDO-based database connector.
 * 
 * Responsibilities:
 * - Accepts database connection parameters
 * - Creates and holds a PDO instance
 * - Provides access to PDO via getPDO()
 * 
 * This class is immutable and uses readonly properties.
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
        'sqlsrv' => 1433,   // MS SQL Server
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
     * @param string $driver Supported database driver ('mysql', 'pgsql', 'sqlsrv')
     * @param string $hostname Database host
     * @param string $db_name Database name
     * @param string $db_user Database username
     * @param string $db_password Database password
     * @param int|null $port Optional. Database port; defaults to standard port for the driver
     * 
     * @throws PDOException If connection fails
     */
    public function __construct(
        string $driver, 
        string $hostname, 
        string $db_name, 
        string $db_user, 
        string $db_password, 
        ?int $port = null
    ) {

        // Validate and parse driver
        $driver = match (strtolower(trim($driver))) {
            'mysql'  => DbDriver::MYSQL,
            'pgsql'  => DbDriver::PGSQL,
            'sqlsrv' => DbDriver::SQLSRV,
            default  => throw new PDOException("Unsupported database driver: $driver"),
        };
        
        // Use provided port or default port for driver
        $port = $port ?? self::DEFAULT_PORTS[$driver->value];

        // Build DSN string based on driver
        $dsn = match ($driver) {
            DbDriver::MYSQL     => "mysql:host=$hostname;port=$port;dbname=$db_name;charset=utf8mb4",
            DbDriver::PGSQL     => "pgsql:host=$hostname;port=$port;dbname=$db_name",
            DbDriver::SQLSRV    => "sqlsrv:Server=$hostname,$port;Database=$db_name",
        };

        // PDO options
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        // Attempt connection
        try {
            $this->pdo = new PDO($dsn, $db_user, $db_password, $options);
        } catch (PDOException $e) {
            throw new PDOException(
                "Database connection failed for driver {$driver->value} at {$hostname}/{$db_name}: " . $e->getMessage());
        }
    }

    /**
     * Returns the PDO instance.
     * 
     * Controllers / models / services can use this for database queries.
     * 
     * @return PDO
     */
    public function getPDO(): PDO
    {
        return $this->pdo;
    }
}
