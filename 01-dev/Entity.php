<?php
/**
 * Class Entity
 *
 * This class represents a generic entity for interacting with a relational database
 * using PDO for CRUD operations. It provides methods for inserting, updating,
 * deleting, and selecting database records. The class can hydrate its properties
 * from database results and filter out non-database properties when performing
 * database operations. It supports dynamic entity creation based on the
 * associated table's column names and types.
 *
 * @package Entity
 */
declare(strict_types=1);
namespace Crystal\Components;

use PDO;
use PDOStatement;
use PDOException;
use DateTimeInterface;
use JsonSerializable;
use ReflectionClass;
use LogicException;

abstract class Entity implements JsonSerializable
{
    protected static ?PDO $dbConnection;
    protected ?int $id = null;

    /**
     * Mass hydrate from array.
     *
     * This method serves as a convenience wrapper to hydrate multiple entities from an array of data.
     * It internally uses the `hydrate()` method to hydrate individual entities, but allows you to
     * hydrate multiple entities in a single call.
     *
     * @param array $data The array of data to hydrate into entities.
     * @return array The array of hydrated entity objects.
     */
    public static function fromArray(array $data) : array
    {
        $entities = [];
        foreach($data as $entity){
            $entities[] = (new static)->hydrate($entity);
        }
        return $entities;
    }

    /**
     * Sets the database connection.
     * @param PDO $dbConnection The PDO database connection instance.
     * @return void
     */
    public static function SetConnection(PDO $dbConnection): void
    {
        static::$dbConnection = $dbConnection;
    }

    /**
     * Converts the class name of the entity to its corresponding database table name in snake_case format.
     * This method takes the fully qualified class name, extracts the class name without the namespace,
     * and then converts it to a table name convention commonly used in databases (snake_case).
     * For example, for a class `App\Models\FXExchangeRates`, it will return `fx_exchange_rates`.
     *
     * @return string The corresponding database table name in snake_case.
     */
    public static function getTableName(): string
    {
        $shortName = substr(strrchr(static::class, '\\'), 1); // Get the class name without namespace
        return strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $shortName));
    }

    /**
     * Retrieves a single entity by its ID.
     *
     * @param int           $id The ID of the entity to retrieve.
     * @return static|null  The entity instance if found, or null if not found.
     */
    public static function find(int $id) : ?static
    {
        // Get table name
        $tableName = defined(static::class.'::TABLE_NAME') ? constant(static::class.'::TABLE_NAME') : static::getTableName();

        // Get entity data from db with check for soft deletes
        $stmt = static::$dbConnection->prepare("SELECT `$tableName`.* FROM `$tableName` WHERE `id` = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        // Return null if no record is found
        return ($data)
            ? (new static())->hydrate($data)
            : null;
    }

    /*
     * Static factory method to create an instance of the current class
     *
     * @return static
     */
    public static function create(): static {
        return new static(); // Return an instance of the called entity subclass
    }

    /**
     * Inserts a new entity into the database.
     *
     * This method inserts a new record into the database based on the entity's properties. It automatically sets the
     * `created_at` timestamp and ensures the `id` field is not set before insertion. If insertion is successful,
     * the entity instance is returned with the `id` field populated. If the insert fails, a `PDOException` is thrown.
     *
     * @return static The current entity instance (with `id` set) on success.
     * @throws LogicException If the entity already has an `id` set (this is forbidden for new entities).
     * @throws PDOException If there is a database error during the insert operation.
     */
    public function insert() : static
    {
        // Forbid inserting if id is somehow set.
        if(isset($this->id)) throw new LogicException("Entity Id cannot be set when inserting");

        // Get table name
        $tableName = defined(static::class.'::TABLE_NAME') ? constant(static::class.'::TABLE_NAME') : static::getTableName();

        // Get instantiated entity properties that exist in database (property name = db field name)
        $properties = static::filterDatabaseProperties($this, $tableName);

        // Prepare the SQL query with dynamic fields for the INSERT statement
        $fields = [];
        $placeholders = [];
        $bindValues = [];

        // Loop through the properties to build the fields and placeholders for the query
        foreach ($properties as $field => $val) {
            $fields[] = "$tableName.$field"; // Add field to columns
            $placeholders[] = ":$field"; // Add placeholder for each field
            $bindValues[":$field"] = $val; // Map placeholder to value
        }

        // Prepare query and bind parameters
        $sql = "INSERT INTO `$tableName` (" . implode(", ", $fields) . ") VALUES (" . implode(", ", $placeholders) . ")";
        $stmt = static::prepareStatement($sql, $bindValues);

        // Execute the insert statement and return the new entity with id set
        try {
            $stmt->execute();
            $this->id = (int)static::$dbConnection->lastInsertId(); // Fetch the last inserted ID and assign it to the object property
            return $this;
        } catch (PDOException $e) {
            error_log($e->getMessage());
            throw new PDOException("Entity insert error: " . $e->getMessage());
        }
    }

    /**
     * Updates an existing entity in the database.
     *
     * This method updates the entity in the database by setting its fields to the current values of the entity object.
     * The `updated_at` timestamp is automatically updated to the current date and time.
     *
     * @return static The updated entity instance on success.
     * @throws \LogicException If the entity does not have an `id` set (this is required for updates).
     * @throws \PDOException If there is a database error during the update operation.
     */
    public function update() : static
    {
        // Early exit if id is not set
        $this->id ?? throw new \LogicException("Missing Entity Id on update");

        // Get table name
        $tableName = defined(static::class.'::TABLE_NAME') ? constant(static::class.'::TABLE_NAME') : static::getTableName();

        // Get the properties of the instantiated object
        $properties = static::filterDatabaseProperties($this, $tableName);

        // Loop through the properties to build the SET part of the query
        $setClause = [];
        $bindValues = [];
        foreach ($properties as $field => $val) {
            $setClause[] = "`$tableName`.`$field` = :{$tableName}_{$field}";  // field = :field_name
            $bindValues[":{$tableName}_{$field}"] = $val;    // associate :field_name with the actual value
        }

        // Prepare query and bind parameters
        $sql = "UPDATE `$tableName` SET " . implode(", ", $setClause) . " WHERE id = :whereId";
        $stmt = static::prepareStatement($sql, $bindValues); // prepare statement with bind values
        $stmt->bindParam(':whereId',  $this->id, PDO::PARAM_INT); // additionaly, bind the id for the WHERE clause

        // Execute
        try {
            $stmt->execute();
            return $this;
        } catch (PDOException $e) {
            error_log($e->getMessage());
            throw new PDOException("Entity update error: " . $e->getMessage());
        }
    }

    /**
     * Deletes the current entity from the database.
     *
     * This method removes the entity from the database using its ID.
     * If the entity does not have an ID set, a LogicException is thrown.
     *
     * @throws          LogicException If the entity ID is not set.
     * @return bool     Returns true on successful deletion, false on failure.
     */
    public function delete(): bool
    {
        if (!isset($this->id)) throw new LogicException("Entity ID is required for deletion");
        $tableName = defined(static::class.'::TABLE_NAME') ? constant(static::class.'::TABLE_NAME') : static::getTableName();
        $stmt = static::$dbConnection->prepare("DELETE FROM `$tableName` WHERE `id` = :id");
        $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Get Id
     */
    public function getId() : ?int
    {
        return $this->id;
    }

    /**
     * Hydrates the current instance with data from an array.
     *
     * This method maps the given array data to the instance's properties, setting each property
     * with the corresponding value from the array. It handles basic data types such as `string`, `int`,
     * `bool`, and `DateTime`. The method supports automatic type conversion based on the property type.
     *
     * @param array     $modelData The associative array of data to map to the object properties.
     * @return static   The current instance, allowing for method chaining.
     */
    public function hydrate(array $modelData): static
    {
        $reflectionProps = (new ReflectionClass($this))->getProperties(\ReflectionProperty::IS_PUBLIC | \ReflectionProperty::IS_PROTECTED);

        foreach ($reflectionProps as $property) {

            $propertyName = $property->getName();

            // Skip properties not present in $modelData
            if (!array_key_exists($propertyName, $modelData)) continue;

            $val = $modelData[$propertyName];
            $type = $property->getType()?->getName();
            //$docComment = $property->getDocComment();

            $property->setValue($this, match($type) {
                'DateTime', 'DateTimeImmutable' => ($date = $type::createFromFormat('Y-m-d H:i:s', (string)$val)) !== false ? $date : null,
                'bool'     => filter_var($val, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false, // convert true|false to 1|0
                'int'      => (int)$val,
                'float' => (float) $val,
                default    => $val,
            });
        }
        return $this;
    }

    /**
     * Creates and binds values to a PDO statement, handling various types such as DateTime, booleans, and null values.
     *
     * @param string    $sql
     * @param array     $bindValues
     * @return          PDOStatement
     */
    private static function prepareStatement(string $sql, array $bindValues) : PDOStatement
    {
        $stmt = static::$dbConnection->prepare($sql);

        foreach ($bindValues as $placeholder => $val) {
            match(true) {
                $val instanceof \DateTimeInterface => $stmt->bindValue($placeholder, $val->format('Y-m-d H:i:s'), PDO::PARAM_STR),
                $val === null => $stmt->bindValue($placeholder, null, PDO::PARAM_NULL),
                is_array($val) || is_object($val) => $stmt->bindValue($placeholder, json_encode($val, JSON_THROW_ON_ERROR), PDO::PARAM_STR),
                is_int($val) || is_bool($val) => $stmt->bindValue($placeholder, (int)$val, PDO::PARAM_INT),
                is_float($val) => $stmt->bindValue($placeholder, (string)$val, PDO::PARAM_STR),
                default => $stmt->bindValue($placeholder, $val, PDO::PARAM_STR)
            };
        }
        return $stmt;
    }

    /**
     * Extracts and returns the properties of the given class that match the columns in the specified database table.
     * Only non-readonly properties that correspond to database fields are included in the returned array.
     *
     * @param object $class The instance of the class whose properties are being filtered.
     * @param string $tableName The name of the database table corresponding to the entity.
     * @return array An associative array where the keys are the property names that match table columns,
     *               and the values are the property values from the class.
     */
    private static function filterDatabaseProperties(object $class, string $tableName): array
    {
        // Get the list of database columns for the given table
        $stmt = static::$dbConnection->prepare("DESCRIBE `$tableName`");
        $stmt->execute();
        $tableColumns = array_flip($stmt->fetchAll(PDO::FETCH_COLUMN)); // switch key:pair for easier comparison later

        // Reflect the properties of the entity
        $reflectionProps = (new ReflectionClass($class))->getProperties(\ReflectionProperty::IS_PUBLIC | \ReflectionProperty::IS_PROTECTED);

        // Loop through each property and check if it's readonly
        $filteredProperties = [];
        foreach ($reflectionProps as $property) {
            if ($property->isReadonly()) continue; // Skip readonly properties

            $propertyName = $property->getName();

            // Only include properties that exist in the table columns
            if (isset($tableColumns[$propertyName])) {
                // Get the value of the property from the entity and add it to the filtered array
                $filteredProperties[$propertyName] = $class->$propertyName;
            }
        }

        return $filteredProperties;
    }

    /**
     * Serializes the entity to a JSON-compatible format.
     * @return array The serialized entity as an associative array.
     */
    public function jsonSerialize() : array
    {
        $data = get_object_vars($this);

        // Convert DateTimeInterface objects to strings for JSON
        foreach ($data as $key => $val) {
            if ($val instanceof DateTimeInterface) {
                $data[$key] = $val->format('Y-m-d H:i:s');
            }
        }
        return $data;
    }
}
