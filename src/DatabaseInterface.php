<?php

namespace TechWilk\Database;

interface DatabaseInterface
{
    /**
     * Run a sql query on the database
     *
     * @param Query $query
     * @return DatabaseResultInterface
     */
    public function runQuery(Query $query): DatabaseResultInterface;

    /**
     * Perform SQL query
     *
     * @param string $sql with question mark syntax for parameters
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
     * Create and execute an INSERT statement with ON DUPLICATE KEY UPDATE clause
     *
     * @param string $table
     * @param array $data to insert (key => value pairs)
     * @param array $onDuplicate data to update on duplicate (optional)
     *
     * @return void
     */
    public function insertOnDuplicate(string $table, array $data, array $onDuplicate = []);

    /**
     * Create and execute an UPDATE statement
     *
     * @param string $table
     * @param array $data to update (key => value pairs)
     * @param array|string $where (key => value pairs)
     * @return int $rowCount
     */
    public function update(string $table, array $data, $where): int;

    /**
     * Create and execute an UPDATE statement on only the fields which have changed
     *
     * @param string $table
     * @param array $data to update (key => value pairs)
     * @param array|string $where (key => value pairs)
     * @return int $rowCount
     */
    public function updateChanges(string $table, array $data, $where): int;

    /**
     * Create and execute DELETE statement
     *
     * @param string $table
     * @param array|string $where (use '1=1' to delete entire table contents)
     * @return int rows affected
     */
    public function delete(string $table, $where): int;

    public function lastInsertId(): int;
}
