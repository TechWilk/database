<?php

declare(strict_types=1);

namespace TechWilk\Database\Pdo;

use TechWilk\Database\DatabaseErrorMapper;
use TechWilk\Database\DatabaseInterface;
use TechWilk\Database\Exception\DatabaseException;
use TechWilk\Database\Exception\EmptyQueryException;
use TechWilk\Database\MySqlQueryHelpersTrait;
use TechWilk\Database\Query;

class PdoDatabase implements DatabaseInterface
{
    use MySqlQueryHelpersTrait;

    private bool $logQueries = false;

    private array $queries = [];

    public function __construct(
        protected \PDO $pdo,
    ) {
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    public static function connectToMySql(
        string $host,
        string $database,
        string $username,
        string $password,
        bool $usePersistentConnection = false,
        int $port = null
    ): self {
        $dsn = 'mysql:' . implode(';', [
            'host=' . $host,
            'dbname=' . $database,
            $port !== null && $port !== 0 ? 'port=' . $port : null,
            'charset=utf8mb4',
        ]);

        try {
            $pdo = new \PDO($dsn, $username, $password, [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_PERSISTENT => $usePersistentConnection,
            ]);

            return new self($pdo);
        } catch (\PDOException $pdoException) {
            throw self::createExceptionFromPdoException($pdoException);
        }
    }

    /**
     * Run a sql query on the database.
     */
    public function runQuery(Query $query): PdoDatabaseResult
    {
        return $this->query($query->getSql(), $query->getParameters());
    }

    /**
     * Perform SQL query.
     *
     * @param string  $sql    with question mark syntax for parameters
     * @param mixed[] $params
     */
    public function query(string $sql, array $params = []): PdoDatabaseResult
    {
        try {
            $startTime = microtime(true);

            if ('' === $sql) {
                throw new EmptyQueryException('Query was empty');
            }

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
        } catch (\PDOException $pdoException) {
            throw self::createExceptionFromPdoException($pdoException);
        }
    }

    public function escape(string $value): string
    {
        throw new DatabaseException('Database::escape() is no longer a valid function. Replace with bound parameters (question mark syntax).');
    }

    public function lastInsertId(): int
    {
        return (int) $this->pdo->lastInsertId();
    }

    private static function createExceptionFromPdoException(\PDOException $exception): DatabaseException
    {
        $sqlState = isset($exception->errorInfo[0]) && is_string($exception->errorInfo[0])
            ? $exception->errorInfo[0]
            : null;
        $driverCode = isset($exception->errorInfo[1]) ? (int) $exception->errorInfo[1] : 0;

        return DatabaseErrorMapper::createException(
            'PDO Error: (' . $driverCode . '). ' . $exception->getMessage(),
            $sqlState,
            $driverCode,
            $exception
        );
    }

    public function __destruct()
    {
        if ($this->logQueries) {
            var_dump($this->queries);
        }
    }
}
