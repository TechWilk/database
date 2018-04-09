<?php

namespace TechWilk\Database;

interface DatabaseResultInterface
{
    /**
     * Fetches next row
     *
     * alias of self::fetchObject()
     */
    public function fetch(string $className = 'stdClass', array $params = []);

    /**
     * Fetches next row as an object
     *
     * @throws NoRowException
     */
    public function fetchObject(string $className = 'stdClass', array $params = []);

    /**
     * Fetches next row
     *
     * @throws NoRowException
     */
    public function fetchArray();

    /**
     * Fetches all rows
     *
     * @throws NoRowException
     */
    public function fetchAll(string $className = 'stdClass', array $params = []): array;

    /**
     * Fetches all rows as an array
     *
     * @throws NoRowException
     */
    public function fetchAllObject(string $className = 'stdClass', array $params = []): array;

    /**
     * Fetches all rows
     *
     * @throws NoRowException
     */
    public function fetchAllArray(): array;

    /**
     * Fetch Column
     *
     * Fetches data from a single column in the result set.
     * Will only return NOT NULL values
     *
     * @throws NoRowException
     */
    public function fetchColumn(string $column);

    /**
     * Checks if the result is empty
     */
    public function isEmpty(): bool;

    /**
     * Number of affected/fetched rows
     *
     * @return int $rowCount
     */
    public function rowCount(): int;

    /**
     * Resets the pointer for data seeking
     */
    public function reset();

    // ---- aliases for people used to the "get" syntax ----
    // ---- implemented in AbstractDatabaseInterface ----

    /**
     * Alias for fetch()
     * @see self::fetch()
     */
    public function get();

    /**
     * Alias for fetchAll()
     * @see self::fetchAll()
     */
    public function getAll(string $className = 'stdClass', array $params = []): array;

    /**
     * Alias for fetchAllObject()
     * @see self::fetchAllObject()
     */
    public function getObject(string $className = 'stdClass', array $params = []);

    /**
     * Alias for fetchAllArray()
     * @see self::fetchAllArray()
     */
    public function getArray();

    /**
     * Alias for fetchColumn()
     * @see self::fetchColumn()
     */
    public function getColumn(string $column);

    /**
     * Returns the SQL statement run on the database
     *
     * @return string
     */
    public function getSql();
}
