<?php

declare(strict_types=1);

namespace TechWilk\Database;

use TechWilk\Database\Exception\DatabaseException;

interface DatabaseResultInterface
{
    /**
     * Fetches next row.
     *
     * alias of self::fetchObject()
     */
    public function fetch(string $className = \stdClass::class, array $params = []);

    /**
     * Fetches next row as an object.
     *
     * @throws DatabaseException
     */
    public function fetchObject(string $className = \stdClass::class, array $params = []);

    /**
     * Fetches next row.
     *
     * @throws DatabaseException
     */
    public function fetchArray($type);

    /**
     * Fetches all rows.
     *
     * @throws DatabaseException
     */
    public function fetchAll(string $className = \stdClass::class, array $params = []): array;

    /**
     * Fetches all rows as an array.
     *
     * @throws DatabaseException
     */
    public function fetchAllObject(string $className = \stdClass::class, array $params = []): array;

    /**
     * Fetches all rows.
     *
     * @throws DatabaseException
     */
    public function fetchAllArray(): array;

    /**
     * Fetch Column.
     *
     * Fetches data from a single column in the result set.
     * Will only return NOT NULL values
     *
     * @throws DatabaseException
     */
    public function fetchColumn(string $column);

    /**
     * Checks if the result is empty.
     */
    public function isEmpty(): bool;

    /**
     * Number of affected/fetched rows.
     *
     * @return int $rowCount
     */
    public function rowCount(): int;

    /**
     * Resets the pointer for data seeking.
     */
    public function reset();

    // ---- aliases for people used to the "get" syntax ----
    // ---- implemented in AbstractDatabaseInterface ----

    /**
     * Alias for fetch().
     *
     * @see self::fetch()
     */
    public function get();

    /**
     * Alias for fetchAll().
     *
     * @see self::fetchAll()
     */
    public function getAll(string $className = \stdClass::class, array $params = []): array;

    /**
     * Alias for fetchAllObject().
     *
     * @see self::fetchAllObject()
     */
    public function getObject(string $className = \stdClass::class, array $params = []);

    /**
     * Alias for fetchAllArray().
     *
     * @see self::fetchAllArray()
     */
    public function getArray();

    /**
     * Alias for fetchColumn().
     *
     * @see self::fetchColumn()
     */
    public function getColumn(string $column);

    /**
     * Returns the SQL statement run on the database.
     *
     * @return string
     */
    public function getSql();
}
