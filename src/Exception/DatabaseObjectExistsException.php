<?php

declare(strict_types=1);

namespace TechWilk\Database\Exception;

/**
 * Thrown when creating an object which already exists (table, column, etc.).
 *
 * This is a category parent. Available children are:
 * - none currently
 *
 * SQLSTATE and driver specific error codes:
 *
 * @see self::MYSQL_ER_TABLE_EXISTS_ERROR
 * @see self::SQLSTATE_BASE_TABLE_OR_VIEW_ALREADY_EXISTS
 */
class DatabaseObjectExistsException extends DatabaseException
{
    /**
     * Table already exists.
     *
     * {@link https://dev.mysql.com/doc/mysql-errors/8.0/en/server-error-reference.html#error_er_table_exists_error}
     */
    public const MYSQL_ER_TABLE_EXISTS_ERROR = 1050;

    /**
     * Base table or view already exists.
     */
    public const SQLSTATE_BASE_TABLE_OR_VIEW_ALREADY_EXISTS = '42S01';
}
