<?php

declare(strict_types=1);

namespace TechWilk\Database\Pdo;

use TechWilk\Database\DatabaseInterface;
use TechWilk\Database\DatabaseResultInterface;
use TechWilk\Database\Exception\BadFieldException;
use TechWilk\Database\Exception\DatabaseException;
use TechWilk\Database\Query;
use TechWilk\Database\QuerySegment;

class PdoDatabase implements DatabaseInterface
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

    protected $pdo;
    private $logQueries = false;
    private $queries = [];

    public function __construct(
        string $host,
        string $database,
        string $username,
        string $password,
        bool $usePersistentConnection = false
    ) {
        $dsn = 'mysql:host=' . $host . ';dbname=' . $database . ';charset=utf8mb4';

        $this->pdo = new \PDO($dsn, $username, $password, [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_PERSISTENT => $usePersistentConnection,
        ]);
    }

    /**
     * Run a sql query on the database.
     *
     * @return PdoDatabaseResult
     */
    public function runQuery(Query $query): DatabaseResultInterface
    {
        return $this->query($query->getSql(), $query->getParameters());
    }

    /**
     * Perform SQL query.
     *
     * @param string  $sql    with question mark syntax for parameters
     * @param mixed[] $params
     *
     * @return PdoDatabaseResult
     */
    public function query(string $sql, array $params = []): DatabaseResultInterface
    {
        $startTime = microtime(true);

        $stmt = $this->pdo->prepare($sql);

        $i = 1;
        foreach ($params as $param) {
            if (is_int($param)) {
                $stmt->bindValue($i, $param, \PDO::PARAM_INT);
            } elseif (is_bool($param)) {
                $stmt->bindValue($i, $param, \PDO::PARAM_BOOL);
            } elseif (is_null($param)) {
                $stmt->bindValue($i, $param, \PDO::PARAM_NULL);
            } else {
                $stmt->bindValue($i, $param, \PDO::PARAM_STR);
            }

            ++$i;
        }

        $stmt->execute();

        if ($this->logQueries) {
            $this->queries[] = [
                'sql' => $sql,
                'time' => microtime(true) - $startTime,
            ];
        }

        return new PdoDatabaseResult($stmt);
    }

    /**
     * Create and execute an INSERT statement.
     *
     * @param array[] $dataArrays (each an array of key => value pairs)
     *
     * @return int insert id if only one insert, insert id of first if multiple inserts
     */
    public function insert(string $table, array ...$dataArrays)
    {
        $querySegment = $this->createInsertSql($table, ...$dataArrays);
        $result = $this->query($querySegment->getSql(), $querySegment->getParameters());

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
     * @param array|string $where (use '1=1' to delete entire table contents)
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

    public function escape(string $value): string
    {
        throw new DatabaseException('Database::escape() is no longer a valid function. Replace with bound parameters (question mark syntax).');
    }

    public function lastInsertId(): int
    {
        return (int) $this->pdo->lastInsertId();
    }

    public function __destruct()
    {
        if ($this->logQueries) {
            var_dump($this->queries);
        }
    }
}
