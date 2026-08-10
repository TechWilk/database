<?php

declare(strict_types=1);

namespace TechWilk\Database\Exception;

/**
 * Thrown when the database rejects a write because a UNIQUE or PRIMARY KEY value already exists.
 *
 * @see self::MYSQL_ER_DUP_ENTRY
 */
class DuplicateDatabaseRecordException extends IntegrityConstraintException
{
    /**
     * Duplicate entry for a key.
     *
     * {@link https://dev.mysql.com/doc/mysql-errors/8.0/en/server-error-reference.html#error_er_dup_entry}
     */
    public const MYSQL_ER_DUP_ENTRY = 1062;
}
