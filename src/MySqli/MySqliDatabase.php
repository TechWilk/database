<?php

declare(strict_types=1);

namespace TechWilk\Database\MySqli;

use TechWilk\Database\DatabaseErrorMapper;
use TechWilk\Database\DatabaseInterface;
use TechWilk\Database\Exception\DatabaseException;
use TechWilk\Database\MySqlSecureTableField;
use TechWilk\Database\ParseDataArray;
use TechWilk\Database\Query;
use TechWilk\Database\QuerySegment;

class MySqliDatabase implements DatabaseInterface
{
    use ParseDataArray;
    use MySqlSecureTableField;

    public function __construct(
        protected \mysqli $mysqli
    ) {
        if ($this->mysqli->connect_errno !== 0) {
            throw self::createExceptionFromMysqliError(
                (string) $this->mysqli->connect_error,
                $this->mysqli->connect_errno,
                $this->mysqli->sqlstate,
            );
        }
    }

    public static function connect(
        string $host,
        string $database,
        string $username,
        string $password,
        int $errorReportingLevel = MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT,
        bool $usePersistentConnection = false,
        ?int $port = null
    ): self {
        if ($usePersistentConnection) {
            $host = 'p:' . $host;
        }

        mysqli_report($errorReportingLevel);

        try {
            $mysqli = new \mysqli($host, $username, $password, $database, $port);

            if ($mysqli->connect_errno !== 0) {
                throw self::createExceptionFromMysqliError(
                    (string) $mysqli->connect_error,
                    $mysqli->connect_errno,
                    $mysqli->sqlstate,
                );
            }

            return new self($mysqli);
        } catch (\mysqli_sql_exception $e) {
            throw self::createExceptionFromMysqliException($e);
        }
    }

    /**
     * Run a sql query on the database.
     */
    public function runQuery(Query $query): MySqliDatabaseResult
    {
        return $this->query($query->getSql(), $query->getParameters());
    }

    /**
     * Perform SQL query.
     *
     * @param string  $sql    with question mark syntax for parameters
     * @param mixed[] $params
     */
    public function query(string $sql, array $params = []): MySqliDatabaseResult
    {
        try {
            /** @var \mysqli_stmt|false $stmt */
            $stmt = $this->mysqli->prepare($sql);

            if (false === $stmt) {
                throw self::createExceptionFromMysqliError(
                    $this->mysqli->error,
                    $this->mysqli->errno,
                    $this->mysqli->sqlstate,
                );
            }

            if ($params !== []) {
                $typeString = '';
                $typeParamArray = [];

                foreach ($params as $param) {
                    if (is_int($param) || is_bool($param)) {
                        $typeString .= 'i';
                        $typeParamArray[] = $param;
                    } elseif (is_float($param)) {
                        $typeString .= 'd';
                        $typeParamArray[] = $param;
                    } else {
                        $typeString .= 's';
                        $typeParamArray[] = $param;
                    }
                }

                if (!$stmt->bind_param($typeString, ...$typeParamArray)) {
                    throw self::createExceptionFromMysqliError(
                        $stmt->error,
                        $stmt->errno,
                        $stmt->sqlstate,
                    );
                }
            }

            if (!$stmt->execute()) {
                throw self::createExceptionFromMysqliError(
                    $stmt->error,
                    $stmt->errno,
                    $stmt->sqlstate,
                );
            }

            return new MySqliDatabaseResult(
                $stmt
            );
        } catch (\mysqli_sql_exception $e) {
            throw self::createExceptionFromMysqliException(
                $e,
                $this->mysqli->sqlstate,
            );
        }
    }

    /**
     * Create and execute an INSERT statement.
     *
     * @param array[] $dataArrays (each an array of key => value pairs)
     *
     * @return int insert id if only one insert, insert id of first if multiple inserts
     *
     * @throws DatabaseException
     */
    public function insert(string $table, array ...$dataArrays): int
    {
        $querySegment = $this->createInsertSql($table, ...$dataArrays);
        $this->query($querySegment->getSql(), $querySegment->getParameters());

        return $this->lastInsertId();
    }

    /**
     * Create and execute an INSERT statement with ON DUPLICATE KEY UPDATE clause.
     *
     * @param array $data        to insert (key => value pairs)
     * @param array $onDuplicate data to update on duplicate (optional)
     */
    public function insertOnDuplicate(string $table, array $data, array $onDuplicate = []): void
    {
        $querySegment = $this->createInsertSql($table, $data);

        $query = Query::fromSegments([
            $querySegment,
            new QuerySegment('ON DUPLICATE KEY UPDATE'),
            $this->parseDataArray($onDuplicate),
        ]);

        $this->runQuery($query);
    }

    /**
     * Create and execute an UPDATE statement.
     *
     * @param array        $data  to update (key => value pairs)
     * @param array|string $where (key => value pairs)
     *
     * @return int $rowCount
     */
    public function update(string $table, array $data, $where): int
    {
        $query = Query::fromSegments([
            new QuerySegment('UPDATE ' . $this->secureTableField($table) . ' SET '),
            $this->parseDataArray($data),
            $this->parseWhere($where),
        ]);

        $result = $this->runQuery($query);

        return $result->rowCount();
    }

    /**
     * Create and execute an UPDATE statement using a where valid IN ().
     */
    public function updateUsingIn(string $table, array $data, array $where): int
    {
        $whereSegment = new QuerySegment('WHERE (');
        $dataSegment = QuerySegment::fieldIn(key($where), reset($where));
        $closingSegment = new QuerySegment(')');

        $finalWhereSegment = $whereSegment->withSegment($dataSegment)->withSegment($closingSegment);

        $query = Query::fromSegments([
            new QuerySegment('UPDATE ' . $this->secureTableField($table) . ' SET '),
            $this->parseDataArray($data),
            $finalWhereSegment,
        ]);

        $result = $this->runQuery($query);

        return $result->rowCount();
    }

    /**
     * Create and execute an UPDATE statement on only the fields which have changed.
     *
     * @param array        $data  to update (key => value pairs)
     * @param array|string $where (key => value pairs)
     *
     * @return int $rowCount
     */
    public function updateChanges(string $table, array $data, $where): int
    {
        // find previous values
        $fields = array_keys($data);
        $fields = array_map(self::class . '::secureTableField', $fields);

        $whereSegment = $this->parseWhere($where);

        $sql = 'SELECT ' . implode(', ', $fields) . ' FROM ' . $this->secureTableField($table) . ' ' . $whereSegment->getSql();
        $result = $this->query($sql, $whereSegment->getParameters());

        if ($result->rowCount() > 1) {
            throw new DatabaseException('Unable to update changes: multiple records found');
        }

        // remove fields which haven't changed
        $previous = $result->fetchArray();

        foreach ($data as $field => $value) {
            if ($previous[$field] == $value) {
                unset($data[$field]);
            }
        }

        // nothing to update
        if ($data === []) {
            return 0;
        }

        return $this->update($table, $data, $where);
    }

    /**
     * Performs a SELECT first, If the record does not exist it will insert using the given data. If the record does
     * exists then it will perform an UPDATE statement on the fields that have changed.
     *
     * @param array        $data  to update (key => value pairs)
     * @param array|string $where (key => value pairs)
     */
    public function selectAndUpdate(string $table, array $data, $where): int
    {
        $fields = array_keys($data);
        $fields = array_map(self::class . '::secureTableField', $fields);

        $whereSegment = $this->parseWhere($where);

        $sql = 'SELECT ' . implode(', ', $fields) . ' FROM ' . $this->secureTableField($table) . ' ' . $whereSegment->getSql();
        $result = $this->query($sql, $whereSegment->getParameters());

        if ($result->rowCount() > 1) {
            throw new DatabaseException('Unable to update changes: multiple records found');
        }

        if (0 == $result->rowCount()) {
            return $this->insert($table, $data);
        }

        // remove fields which haven't changed
        $previous = $result->fetchArray();

        foreach ($data as $field => $value) {
            if ($previous[$field] == $value) {
                unset($data[$field]);
            }
        }

        // nothing to update
        if ($data === []) {
            return 0;
        }

        return $this->update($table, $data, $where);
    }

    /**
     * Create and execute DELETE statement.
     *
     * @param array|string $where (use '1=1' to delete entire table contents)
     *
     * @return int rows affected
     */
    public function delete(string $table, $where): int
    {
        $whereQuerySegment = $this->parseWhere($where);

        $query = Query::fromSegments([
            new QuerySegment('DELETE FROM ' . $this->secureTableField($table)),
            $whereQuerySegment,
        ]);

        $result = $this->runQuery($query);

        return $result->rowCount();
    }

    /**
     * Create and execute DELETE statement using a where valid IN ().
     *
     * @param array|string $where (use '1=1' to delete entire table contents)
     *
     * @return int rows affected
     */
    public function deleteUsingIn(string $table, $where): int
    {
        $whereSegment = new QuerySegment('WHERE (');
        $dataSegment = QuerySegment::fieldIn(key($where), reset($where));
        $closingSegment = new QuerySegment(')');

        $finalWhereSegment = $whereSegment->withSegment($dataSegment)->withSegment($closingSegment);

        $query = Query::fromSegments([
            new QuerySegment('DELETE FROM ' . $this->secureTableField($table)),
            $finalWhereSegment,
        ]);

        $result = $this->runQuery($query);

        return $result->rowCount();
    }

    protected function createInsertSql(string $table, array ...$dataArrays): QuerySegment
    {
        if (empty($dataArrays[0])) {
            throw new DatabaseException('No data to insert');
        }

        $fields = array_keys($dataArrays[0]);
        $fields = array_map(self::class . '::secureTableField', $fields);

        $fieldsCount = count($fields);
        $questionMarks = array_fill(0, $fieldsCount, '?');
        $valuesSet = '(' . implode(', ', $questionMarks) . ')';

        $values = [];
        $params = [];
        foreach ($dataArrays as $dataArray) {
            $values[] = $valuesSet;
            foreach ($dataArray as $param) {
                $params[] = $param;
            }
        }

        $sql = 'INSERT INTO ' . $this->secureTableField($table);
        $sql .= ' (' . implode(', ', $fields) . ')';
        $sql .= ' VALUES ' . implode(', ', $values);

        return new QuerySegment($sql, $params);
    }

    /**
     * Parses data and converts to string for WHERE clause.
     *
     * @param array|string $where (use '1=1' to delete entire table contents)
     */
    protected function parseWhere($where): QuerySegment
    {
        $whereSegment = new QuerySegment('WHERE (');
        $dataSegment = is_array($where) ? $this->parseDataArray($where, ' AND ') : new QuerySegment((string) $where);
        $closingSegment = new QuerySegment(')');

        return $whereSegment->withSegment($dataSegment)->withSegment($closingSegment);
    }

    public function lastInsertId(): int
    {
        return (int) $this->mysqli->insert_id;
    }

    /**
     * Map a MySQLi errno/error/sqlstate triple to a typed library exception.
     *
     * Sole place that builds the library message for MySQLi driver failures so
     * false-return and exception report modes stay consistent.
     */
    protected static function createExceptionFromMysqliError(
        string $error,
        int $errno,
        ?string $sqlState,
        ?\Throwable $previous = null
    ): DatabaseException {
        return DatabaseErrorMapper::createException(
            'Mysqli Error: (' . $errno . '). ' . $error,
            ($sqlState !== null && $sqlState !== '') ? $sqlState : null,
            $errno,
            $previous
        );
    }

    /**
     * Unwrap {@see \mysqli_sql_exception} and map via {@see createExceptionFromMysqliError()}.
     */
    protected static function createExceptionFromMysqliException(
        \mysqli_sql_exception $exception,
        ?string $fallbackSqlState = null
    ): DatabaseException {
        $sqlState = method_exists($exception, 'getSqlState')
            ? $exception->getSqlState()
            : $fallbackSqlState;

        return self::createExceptionFromMysqliError(
            $exception->getMessage(),
            (int) $exception->getCode(),
            $sqlState,
            $exception
        );
    }

    public function __destruct()
    {
        $this->mysqli->close();
    }
}
