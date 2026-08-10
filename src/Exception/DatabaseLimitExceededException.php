<?php

declare(strict_types=1);

namespace TechWilk\Database\Exception;

/**
 * Thrown when a server or connection capacity limit is exceeded.
 *
 * This is a category parent. Available children are:
 * - none currently
 *
 * SQLSTATE and driver specific error codes:
 *
 * @see self::MYSQL_ER_CON_COUNT_ERROR
 */
class DatabaseLimitExceededException extends DatabaseException
{
    /**
     * Too many connections.
     *
     * {@link https://dev.mysql.com/doc/mysql-errors/8.0/en/server-error-reference.html#error_er_con_count_error}
     */
    public const MYSQL_ER_CON_COUNT_ERROR = 1040;
}
