<?php

declare(strict_types=1);

namespace TechWilk\Database\MySqli;

use TechWilk\Database\DatabaseErrorMapper;
use TechWilk\Database\DatabaseInterface;
use TechWilk\Database\Exception\DatabaseException;
use TechWilk\Database\MySqlQueryHelpersTrait;
use TechWilk\Database\Query;

class MySqliDatabase implements DatabaseInterface
{
    use MySqlQueryHelpersTrait;

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
