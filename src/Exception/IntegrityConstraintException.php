<?php

declare(strict_types=1);

namespace TechWilk\Database\Exception;

/**
 * Thrown when a statement violates an integrity constraint.
 *
 * This is a category parent. Available children are:
 *
 * @see CheckConstraintException
 * @see DuplicateDatabaseRecordException
 * @see ForeignKeyConstraintException
 * @see NullConstraintException
 *
 * SQLSTATE and driver specific error codes:
 *
 * @see self::SQLSTATE_CLASS
 * @see self::SQLSTATE_INTEGRITY_CONSTRAINT_VIOLATION
 */
class IntegrityConstraintException extends DatabaseException
{
    /**
     * SQLSTATE class: integrity constraint violation.
     */
    public const SQLSTATE_CLASS = '23';

    /**
     * Integrity constraint violation (no subclass).
     *
     * {@link https://dev.mysql.com/doc/mysql-errors/8.0/en/server-error-reference.html#error_er_dup_entry}
     */
    public const SQLSTATE_INTEGRITY_CONSTRAINT_VIOLATION = '23000';
}
