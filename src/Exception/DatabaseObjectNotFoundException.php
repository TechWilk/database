<?php

declare(strict_types=1);

namespace TechWilk\Database\Exception;

/**
 * Thrown when a referenced database object (table, column, schema, etc.) does not exist.
 *
 * This is a category parent. Available children are:
 *
 * @see InvalidTableException

 * SQLSTATE and driver specific error codes:
 *
 * @see self::MYSQL_ER_BAD_DB_ERROR
 * @see self::MYSQL_ER_NO_DB_ERROR
 * @see self::SQLSTATE_INVALID_CATALOG_NAME
 */
class DatabaseObjectNotFoundException extends DatabaseException
{
    /**
     * Unknown database.
     *
     * MySQL reports SQLSTATE 42000 for this errno (except 3D000).
     *
     * {@link https://dev.mysql.com/doc/mysql-errors/8.0/en/server-error-reference.html#error_er_bad_db_error}
     */
    public const MYSQL_ER_BAD_DB_ERROR = 1049;

    /**
     * No database selected.
     *
     * {@link https://dev.mysql.com/doc/mysql-errors/8.0/en/server-error-reference.html#error_er_no_db_error}
     */
    public const MYSQL_ER_NO_DB_ERROR = 1046;

    /**
     * Invalid catalog name (MySQL: no database selected).
     *
     * {@link https://dev.mysql.com/doc/refman/8.0/en/error-message-elements.html}
     */
    public const SQLSTATE_INVALID_CATALOG_NAME = '3D000';
}
