<?php

declare(strict_types=1);

namespace TechWilk\Database;

use TechWilk\Database\Exception\{
    DatabaseAccessDeniedException,
    DatabaseConnectionException,
    DatabaseDataException,
    DatabaseDeadlockException,
    DatabaseException,
    DatabaseFeatureNotSupportedException,
    DatabaseLimitExceededException,
    DatabaseObjectExistsException,
    DatabaseObjectNotFoundException,
    DatabaseQueryException,
    DatabaseReadOnlyException,
    DatabaseTransactionException,
    DatabaseTransactionRollbackException,
    DuplicateDatabaseRecordException,
    EmptyQueryException,
    IntegrityConstraintException,
    InvalidTableException,
};

final class DatabaseErrorMapper
{
    /**
     * Create the most appropriate database exception for a driver error.
     *
     * Callers should throw the returned instance. Exception code is always $driverCode
     * (e.g. MySQL errno).
     */
    public static function createException(
        string $message,
        ?string $sqlState,
        int $driverCode = 0,
        ?\Throwable $previous = null
    ): DatabaseException {
        $previousException = $previous instanceof \Exception ? $previous : null;

        return self::map($message, $sqlState, $driverCode, $previousException);
    }

    private static function map(
        string $message,
        ?string $sqlState,
        int $driverCode,
        ?\Exception $previous
    ): DatabaseException {
        $exceptionFromDriverCode = self::fromDriverCode($message, $sqlState, $driverCode, $previous);
        if (null !== $exceptionFromDriverCode) {
            return $exceptionFromDriverCode;
        }

        if (null !== $sqlState && $sqlState !== '') {
            $exceptionFromSqlState = self::fromSqlState($message, $sqlState, $driverCode, $previous);
            if (null !== $exceptionFromSqlState) {
                return $exceptionFromSqlState;
            }
        }

        return new DatabaseException($message, $sqlState, $driverCode, $previous);
    }

    /**
     * Driver-code rows and special-cases that must win over coarse SQLSTATE categories.
     */
    private static function fromDriverCode(
        string $message,
        ?string $sqlState,
        int $driverCode,
        ?\Exception $previous
    ): ?DatabaseException {
        switch ($driverCode) {
            case DatabaseQueryException::MYSQL_ER_NON_UNIQ_ERROR:
                return new DatabaseQueryException($message, $sqlState, $driverCode, $previous);

            case DatabaseObjectNotFoundException::MYSQL_ER_BAD_DB_ERROR:
            case DatabaseObjectNotFoundException::MYSQL_ER_NO_DB_ERROR:
                return new DatabaseObjectNotFoundException($message, $sqlState, $driverCode, $previous);

            case DuplicateDatabaseRecordException::MYSQL_ER_DUP_ENTRY:
                return new DuplicateDatabaseRecordException($message, $sqlState, $driverCode, $previous);

            case DatabaseDeadlockException::MYSQL_ER_LOCK_DEADLOCK:
                return new DatabaseDeadlockException($message, $sqlState, $driverCode, $previous);

            case InvalidTableException::MYSQL_ER_NO_SUCH_TABLE:
                return new InvalidTableException($message, $sqlState, $driverCode, $previous);

            case DatabaseObjectExistsException::MYSQL_ER_TABLE_EXISTS_ERROR:
                return new DatabaseObjectExistsException($message, $sqlState, $driverCode, $previous);

            case EmptyQueryException::MYSQL_ER_EMPTY_QUERY:
                return new EmptyQueryException($message, $sqlState, $driverCode, $previous);

            case DatabaseAccessDeniedException::MYSQL_ER_ACCESS_DENIED_ERROR:
                return new DatabaseAccessDeniedException($message, $sqlState, $driverCode, $previous);

            case DatabaseLimitExceededException::MYSQL_ER_CON_COUNT_ERROR:
                return new DatabaseLimitExceededException($message, $sqlState, $driverCode, $previous);

            case DatabaseReadOnlyException::MYSQL_ER_READ_ONLY_TRANSACTION:
            case DatabaseReadOnlyException::MYSQL_ER_OPTION_PREVENTS_STATEMENT:
                return new DatabaseReadOnlyException($message, $sqlState, $driverCode, $previous);

            case DatabaseConnectionException::MYSQL_CR_CONNECTION_ERROR:
            case DatabaseConnectionException::MYSQL_CR_CONN_HOST_ERROR:
            case DatabaseConnectionException::MYSQL_CR_UNKNOWN_HOST:
            case DatabaseConnectionException::MYSQL_CR_SERVER_GONE_ERROR:
            case DatabaseConnectionException::MYSQL_CR_SERVER_LOST:
                return new DatabaseConnectionException($message, $sqlState, $driverCode, $previous);
        }

        return null;
    }

    /**
     * SQLSTATE mapping.
     *
     * Codes are in two parts: the first two characters are the class (category), the last three are the subclass.
     * Each class belongs to one of four categories: "S" denotes "Success" (class 00), "W" denotes "Warning"
     * (class 01), "N" denotes "No data" (class 02), and "X" denotes "Exception" (all other classes).
     * Database vendors may define additional values using [I-Z] or [5-9] as the first class or subclass byte.
     *
     * Note: not all databases strictly follow the standards.
     * Nor do we have exhaustive mappings for all classes and subclasses.
     *
     * Standard-conforming SQLSTATE classes (SQL:2011, via Wikipedia):
     * - 00xxx → successful completion (S)
     * - 01xxx → warning (W)
     * - 02xxx → no data (N)
     * - 07xxx → dynamic SQL error (X)
     * - 08xxx → connection exception (X)
     * - 09xxx → triggered action exception (X)
     * - 0Axxx → feature not supported (X)
     * - 0Dxxx → invalid target type specification (X)
     * - 0Exxx → invalid schema name list specification (X)
     * - 0Fxxx → locator exception (X)
     * - 0Kxxx → resignal when handler not active (X) [SQL/PSM]
     * - 0Lxxx → invalid grantor (X)
     * - 0Mxxx → invalid SQL-invoked procedure reference (X)
     * - 0Nxxx → SQL/XML mapping error (X) [SQL/XML]
     * - 0Pxxx → invalid role specification (X)
     * - 0Sxxx → invalid transform group name specification (X)
     * - 0Txxx → target table disagrees with cursor specification (X)
     * - 0Uxxx → attempt to assign to non-updatable column (X)
     * - 0Vxxx → attempt to assign to ordering column (X)
     * - 0Wxxx → prohibited statement encountered during trigger execution (X)
     * - 0Xxxx → invalid foreign server specification (X) [SQL/MED]
     * - 0Yxxx → pass-through specific condition (X) [SQL/MED]
     * - 0Zxxx → diagnostics exception (X)
     * - 10xxx → XQuery error (X) [SQL/XML]
     * - 20xxx → case not found for case statement (X) [SQL/PSM]
     * - 21xxx → cardinality violation (X)
     * - 22xxx → data exception (X)
     * - 23xxx → integrity constraint violation (X)
     * - 24xxx → invalid cursor state (X)
     * - 25xxx → invalid transaction state (X)
     * - 26xxx → invalid SQL statement name (X)
     * - 27xxx → triggered data change violation (X)
     * - 28xxx → invalid authorisation specification (X)
     * - 2Bxxx → dependent privilege descriptors still exist (X)
     * - 2Cxxx → invalid character set name (X)
     * - 2Dxxx → invalid transaction termination (X)
     * - 2Exxx → invalid connection name (X)
     * - 2Fxxx → SQL routine exception (X)
     * - 2Hxxx → invalid collation name (X)
     * - 30xxx → invalid SQL statement identifier (X)
     * - 33xxx → invalid SQL descriptor name (X)
     * - 34xxx → invalid cursor name (X)
     * - 35xxx → invalid condition number (X)
     * - 36xxx → cursor sensitivity exception (X)
     * - 38xxx → external routine exception (X)
     * - 39xxx → external routine invocation exception (X)
     * - 3Bxxx → savepoint exception (X)
     * - 3Cxxx → ambiguous cursor name (X)
     * - 3Dxxx → invalid catalog name (X)
     * - 3Fxxx → invalid schema name (X)
     * - 40xxx → transaction rollback (X)
     * - 42xxx → syntax error or access rule violation (X)
     * - 44xxx → with check option violation (X)
     * - 45xxx → unhandled user-defined exception (X) [SQL/PSM]
     * - 46xxx → OLB-specific error / Java DDL (X) [SQL/OLB, SQL/JRT]
     * - HWxxx → datalink exception (X) [SQL/MED]
     * - HVxxx → FDW-specific condition (X) [SQL/MED]
     * - HYxxx → CLI-specific condition (X) [SQL/CLI]
     * - HZxxx → reserved for ISO 9579 (RDA)
     *
     * @link https://en.wikipedia.org/wiki/SQLSTATE
     * @link https://dev.mysql.com/doc/refman/8.0/en/error-messages-server.html#error-messages-server-sqlstate
     * @link https://mariadb.com/docs/server/reference/sql-statements/programmatic-compound-statements/programmatic-compound-statements-diagnostics/sqlstate
     */
    private static function fromSqlState(
        string $message,
        string $sqlState,
        int $driverCode,
        ?\Exception $previous
    ): ?DatabaseException {
        switch ($sqlState) {
            case InvalidTableException::SQLSTATE_BASE_TABLE_OR_VIEW_NOT_FOUND:
                return new InvalidTableException($message, $sqlState, $driverCode, $previous);

            case DatabaseObjectExistsException::SQLSTATE_BASE_TABLE_OR_VIEW_ALREADY_EXISTS:
                return new DatabaseObjectExistsException($message, $sqlState, $driverCode, $previous);

            case DatabaseDeadlockException::SQLSTATE_SERIALIZATION_FAILURE:
                return new DatabaseDeadlockException($message, $sqlState, $driverCode, $previous);

            case DatabaseAccessDeniedException::SQLSTATE_INVALID_AUTHORISATION_SPECIFICATION:
                return new DatabaseAccessDeniedException($message, $sqlState, $driverCode, $previous);

            case DatabaseObjectNotFoundException::SQLSTATE_INVALID_CATALOG_NAME:
                return new DatabaseObjectNotFoundException($message, $sqlState, $driverCode, $previous);

            case DatabaseFeatureNotSupportedException::SQLSTATE_FEATURE_NOT_SUPPORTED:
                return new DatabaseFeatureNotSupportedException($message, $sqlState, $driverCode, $previous);

            case DatabaseQueryException::SQLSTATE_SYNTAX_ERROR_OR_ACCESS_RULE_VIOLATION:
                return new DatabaseQueryException($message, $sqlState, $driverCode, $previous);

            case DatabaseReadOnlyException::SQLSTATE_READ_ONLY_SQL_TRANSACTION:
                return new DatabaseReadOnlyException($message, $sqlState, $driverCode, $previous);
        }

        $sqlStateErrorCodeClass = substr($sqlState, 0, 2);

        switch ($sqlStateErrorCodeClass) {
            case DatabaseConnectionException::SQLSTATE_CLASS:
                return new DatabaseConnectionException($message, $sqlState, $driverCode, $previous);

            case DatabaseQueryException::SQLSTATE_CLASS_CARDINALITY:
                return new DatabaseQueryException($message, $sqlState, $driverCode, $previous);

            case DatabaseDataException::SQLSTATE_CLASS:
                return new DatabaseDataException($message, $sqlState, $driverCode, $previous);

            case IntegrityConstraintException::SQLSTATE_CLASS:
                return new IntegrityConstraintException($message, $sqlState, $driverCode, $previous);

            case DatabaseTransactionException::SQLSTATE_CLASS:
                return new DatabaseTransactionException($message, $sqlState, $driverCode, $previous);

            case DatabaseTransactionRollbackException::SQLSTATE_CLASS:
                return new DatabaseTransactionRollbackException($message, $sqlState, $driverCode, $previous);

            case DatabaseQueryException::SQLSTATE_CLASS:
                return new DatabaseQueryException($message, $sqlState, $driverCode, $previous);
        }

        return null;
    }
}
