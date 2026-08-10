<?php

declare(strict_types=1);

namespace TechWilk\Database\Exception;

/**
 * Thrown when querying a table which does not exist.
 *
 * @see self::MYSQL_ER_NO_SUCH_TABLE
 * @see self::SQLSTATE_BASE_TABLE_OR_VIEW_NOT_FOUND
 */
class InvalidTableException extends DatabaseObjectNotFoundException
{
    /**
     * Table doesn't exist.
     *
     * {@link https://dev.mysql.com/doc/mysql-errors/8.0/en/server-error-reference.html#error_er_no_such_table}
     */
    public const MYSQL_ER_NO_SUCH_TABLE = 1146;

    /**
     * Base table or view not found.
     */
    public const SQLSTATE_BASE_TABLE_OR_VIEW_NOT_FOUND = '42S02';
}
