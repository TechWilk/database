<?php

declare(strict_types=1);

namespace TechWilk\Database;

interface DatabaseInterface
{
    /**
     * Run a sql query on the database.
     */
    public function runQuery(Query $query): DatabaseResultInterface;

    /**
     * Perform SQL query.
     *
     * @param string  $sql    with question mark syntax for parameters
     * @param mixed[] $params
     */
    public function query(string $sql, array $params = []): DatabaseResultInterface;

    /**
     * Create and execute an INSERT statement.
     *
     * @param array[] $dataArrays (each an array of key => value pairs)
     *
     * @return int|null insert id if only one insert, null if multiple inserts
     */
    public function insert(string $table, array ...$dataArrays);

    /**
     * Create and execute an INSERT statement with ON DUPLICATE KEY UPDATE clause.
     *
     * @param array $data        to insert (key => value pairs)
     * @param array $onDuplicate data to update on duplicate (optional)
     */
    public function insertOnDuplicate(string $table, array $data, array $onDuplicate = []);

    /**
     * Create and execute an UPDATE statement.
     *
     * @param array        $data  to update (key => value pairs)
     * @param array|string $where (key => value pairs)
     *
     * @return int $rowCount
     */
    public function update(string $table, array $data, array|string $where): int;

    /**
     * Create and execute an UPDATE statement using a where valid IN ().
     */
    public function updateUsingIn(string $table, array $data, array $where): int;

    /**
     * Create and execute an UPDATE statement on only the fields which have changed.
     *
     * @param array        $data  to update (key => value pairs)
     * @param array|string $where (key => value pairs)
     *
     * @return int $rowCount
     */
    public function updateChanges(string $table, array $data, array|string $where): int;

    /**
     * Performs a SELECT first, If the record does not exist it will insert using the given data. If the record does
     * exists then it will perform an UPDATE statement on the fields that have changed.
     *
     * @param array        $data  to update (key => value pairs)
     * @param array|string $where (key => value pairs)
     */
    public function selectAndUpdate(string $table, array $data, array|string $where): int;

    /**
     * Create and execute DELETE statement.
     *
     * @param array|string $where (use '1=1' to delete entire table contents)
     *
     * @return int rows affected
     */
    public function delete(string $table, array|string $where): int;

    /**
     * Create and execute DELETE statement using a where valid IN ().
     *
     * @param array|string $where (use '1=1' to delete entire table contents)
     *
     * @return int rows affected
     */
    public function deleteUsingIn(string $table, array|string $where): int;

    public function lastInsertId(): int;
}
