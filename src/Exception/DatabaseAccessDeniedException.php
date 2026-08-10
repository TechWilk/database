<?php

declare(strict_types=1);

namespace TechWilk\Database\Exception;

/**
 * Thrown when the database server rejects a statement, or a connection, for authorisation reasons.
 *
 * This is a category parent. Available children are:
 * - none currently
 *
 * SQLSTATE and driver specific error codes:
 *
 * @see self::SQLSTATE_INVALID_AUTHORISATION_SPECIFICATION
 * @see self::MYSQL_ER_ACCESS_DENIED_ERROR
 */
class DatabaseAccessDeniedException extends DatabaseException
{
    /**
     * Access denied for user with password.
     *
     * {@link https://dev.mysql.com/doc/mysql-errors/8.0/en/server-error-reference.html#error_er_access_denied_error}
     */
    public const MYSQL_ER_ACCESS_DENIED_ERROR = 1045;

    /**
     * Invalid authorisation specification.
     *
     * {@link https://dev.mysql.com/doc/refman/8.0/en/error-message-elements.html}
     */
    public const SQLSTATE_INVALID_AUTHORISATION_SPECIFICATION = '28000';
}
