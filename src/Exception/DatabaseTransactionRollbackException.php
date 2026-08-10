<?php

declare(strict_types=1);

namespace TechWilk\Database\Exception;

/**
 * Thrown when the server rolls back the current transaction.
 *
 * This is a category parent. Available children are:
 *
 * @see DatabaseDeadlockException
 *
 * SQLSTATE and driver specific error codes:
 *
 * @see self::SQLSTATE_CLASS
 */
class DatabaseTransactionRollbackException extends DatabaseException
{
    /**
     * SQLSTATE class: transaction rollback.
     */
    public const SQLSTATE_CLASS = '40';
}
