<?php

declare(strict_types=1);

namespace TechWilk\Database\Exception;

/**
 * Thrown when the database detects a deadlock and rolls back the transaction.
 *
 * @see self::MYSQL_ER_LOCK_DEADLOCK
 * @see self::SQLSTATE_SERIALIZATION_FAILURE
 */
class DatabaseDeadlockException extends DatabaseTransactionRollbackException
{
    /**
     * Deadlock found when trying to get lock.
     *
     * {@link https://dev.mysql.com/doc/mysql-errors/8.0/en/server-error-reference.html#error_er_lock_deadlock}
     */
    public const MYSQL_ER_LOCK_DEADLOCK = 1213;

    /**
     * Serialization failure (deadlock).
     */
    public const SQLSTATE_SERIALIZATION_FAILURE = '40001';
}
