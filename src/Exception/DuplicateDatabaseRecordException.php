<?php

declare(strict_types=1);

namespace TechWilk\Database\Exception;

/**
 * Thrown when the database rejects a write because a UNIQUE or PRIMARY KEY value already exists.
 *
 * @see self::MYSQL_ER_DUP_KEY
 * @see self::MYSQL_ER_DUP_ENTRY
 * @see self::MYSQL_ER_DUP_UNIQUE
 * @see self::MYSQL_ER_DUP_ENTRY_WITH_KEY_NAME
 */
class DuplicateDatabaseRecordException extends IntegrityConstraintException
{
    /**
     * Duplicate key on write (older / storage-engine form).
     *
     * {@link https://dev.mysql.com/doc/mysql-errors/8.0/en/server-error-reference.html#error_er_dup_key}
     */
    public const MYSQL_ER_DUP_KEY = 1022;

    /**
     * Duplicate entry for a key.
     *
     * {@link https://dev.mysql.com/doc/mysql-errors/8.0/en/server-error-reference.html#error_er_dup_entry}
     */
    public const MYSQL_ER_DUP_ENTRY = 1062;

    /**
     * Can't write — duplicate key in table.
     *
     * {@link https://dev.mysql.com/doc/mysql-errors/8.0/en/server-error-reference.html#error_er_dup_unique}
     */
    public const MYSQL_ER_DUP_UNIQUE = 1169;

    /**
     * Duplicate entry for key name.
     *
     * {@link https://dev.mysql.com/doc/mysql-errors/8.0/en/server-error-reference.html#error_er_dup_entry_with_key_name}
     */
    public const MYSQL_ER_DUP_ENTRY_WITH_KEY_NAME = 1586;
}
