<?php

declare(strict_types=1);

namespace TechWilk\Database\MySqli;

use TechWilk\Database\DatabaseInterface;
use TechWilk\Database\DatabaseResultInterface;
use TechWilk\Database\Exception\BadFieldException;
use TechWilk\Database\Exception\DatabaseException;
use TechWilk\Database\Query;
use TechWilk\Database\QuerySegment;

class MySqliDatabase implements DatabaseInterface
{
    public const VALID_EQUATORS = [
        'LIKE',
        'IS',
        'IS NOT',
        '>',
        '>=',
        '<',
        '<=',
        '!=',

        // modifiers
        '+',
        '-',
    ];

    protected $mysqli;

    public function __construct(
        string $host,
        string $database,
        string $username,
        string $password,
        bool $usePersistentConnection = false
    ) {
        if ($usePersistentConnection) {
            $host = 'p:' . $host;
        }

        $this->mysqli = new \MySqli($host, $username, $password, $database);

        if ($this->mysqli->connect_errno) {
            throw new DatabaseException(
                'Failed to connect to MySQL: (' . $this->mysqli->connect_errno . ') ' . $this->mysqli->connect_error,
                $this->mysqli->connect_errno
            );
        }

        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    }

    /**
     * Run a sql query on the database.
     *
     * @return MySqliDatabaseResult
     */
    public function runQuery(Query $query): DatabaseResultInterface
    {
        return $this->query($query->getSql(), $query->getParameters());
    }

    /**
     * Perform SQL query.
     *
     * @param string $sql with question mark syntax for parameters
     * @param mixed[] $params
     *
     * @return MySqliDatabaseResult
     */
    public function query(string $sql, array $params = []): DatabaseResultInterface
    {
        $stmt = $this->mysqli->prepare($sql);

        if (!empty($params)) {
            $typeString = '';
            $typeParamArray = [];

            foreach ($params as $param) {
                if (is_int($param) || is_bool($param)) {
                    $typeString .= 'i';
                    $typeParamArray[] = $param;
                } elseif (is_double($param)) {
                    $typeString .= 'd';
                    $typeParamArray[] = $param;
                } else {
                    $typeString .= 's';
                    $typeParamArray[] = $param;
                }
            }
            $stmt->bind_param($typeString, ...$typeParamArray);
        }

        $stmt->execute();

        return new MySqliDatabaseResult(
            $stmt
        );
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
    public function insert(string $table, array ...$dataArrays)
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
     *
     * @return void
     */
    public function insertOnDuplicate(string $table, array $data, array $onDuplicate = [])
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
     *
     * @param array $data
     * @param array $where
     *
     * @return int
     */
    public function updateUsingIn(string $table, array $data, array $where): int
    {
        $whereSegment = new QuerySegment('WHERE (');
        $dataSegment = QuerySegment::fieldIn(key($where), reset($where));
        $closingSegment = new QuerySegment(')');

        $finalWhereSegment = $whereSegment->withSegment($dataSegment)->withSegment($closingSegment);

        $query = Query::fromSegments([
            new QuerySegment('UPDATE '.$this->secureTableField($table).' SET '),
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
        if (empty($data)) {
            return 0;
        }

        return $this->update($table, $data, $where);
    }

    /**
     * Performs a SELECT first, If the record does not exist it will insert using the given data. If the record does
     * exists then it will perform an UPDATE statement on the fields that have changed.
     *
     * @param string       $table
     * @param array        $data  to update (key => value pairs)
     * @param array|string $where (key => value pairs)
     *
     * @return int
     */
    public function selectAndUpdate(string $table, array $data, $where): int
    {
        $fields = array_keys($data);
        $fields = array_map(self::class.'::secureTableField', $fields);

        $whereSegment = $this->parseWhere($where);

        $sql = 'SELECT '.implode(', ', $fields).' FROM '.$this->secureTableField($table).' '.$whereSegment->getSql();
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
        if (empty($data)) {
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

    protected function createInsertSql(string $table, array ...$dataArrays)
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
     * @param array|string $data (use '1=1' to delete entire table contents)
     */
    protected function parseWhere($where): QuerySegment
    {
        $whereSegment = new QuerySegment('WHERE (');
        $dataSegment = is_array($where) ? $this->parseDataArray($where, ' AND ') : new QuerySegment((string) $where);
        $closingSegment = new QuerySegment(')');

        return $whereSegment->withSegment($dataSegment)->withSegment($closingSegment);
    }

    /**
     * Parse data array.
     *
     * @param string $glue for the implode()
     *                     use ', ' for SET clauses
     *                     or ' AND ' for WHERE clauses
     *
     * @return QuerySegment
     *
     * @throws DatabaseException
     */
    protected function parseDataArray(array $data, string $glue = ', ')
    {
        if (empty($data)) {
            throw new DatabaseException('No data');
        }

        $sqlSegments = [];
        $parameters = [];
        foreach ($data as $field => $value) {
            $equator = $this->parseEquatorFromField($field);

            // remove equator from field string
            if ('=' !== $equator) {
                $equatorWithSpace = ' ' . $equator;
                $startPosition = strlen($field) - strlen($equatorWithSpace);
                $field = substr($field, 0, $startPosition);
            }

            switch ($equator) {
                case 'IS':
                case 'IS NOT':
                    $sqlSegments[] = $this->secureTableField($field) . ' ' . $equator . ' NULL';
                    break;
                case '+':
                case '-':
                    $sqlSegments[] = $this->secureTableField($field) . ' = ' . $this->secureTableField($field) . ' ' . $equator . ' ?';
                    $parameters[] = $value;
                    break;
                default:
                $sqlSegments[] = $this->secureTableField($field) . ' ' . $equator . ' ?';
                $parameters[] = $value;
            }

        }

        $sql = implode($glue, $sqlSegments);

        return new QuerySegment($sql, $parameters);
    }

    private function parseEquatorFromField(string $field): string
    {
        foreach (self::VALID_EQUATORS as $equator) {
            $equatorWithSpace = ' ' . $equator;
            $startPosition = strlen($field) - strlen($equatorWithSpace);

            if ($startPosition < 0) {
                continue;
            }

            if (false !== strpos($field, $equatorWithSpace, $startPosition)) {

                return $equator;
            }
        }

        return '=';
    }

    /**
     * Returns table or field name surrounded by ` character.
     */
    protected function secureTableField(string $field): string
    {
        if (empty($field)) {
            throw new BadFieldException('Field name contains no characters');
        }

        if (false !== strpos($field, '`')) {
            throw new BadFieldException('Field name must not include ` character');
        }

        return '`' . $field . '`';
    }

    public function lastInsertId(): int
    {
        return (int) $this->mysqli->insert_id;
    }

    public function __destruct()
    {
        $this->mysqli->close();
    }
}
