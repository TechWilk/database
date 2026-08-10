<?php

declare(strict_types=1);

namespace TechWilk\Database\Exception;

/**
 * Thrown when the query string is empty (client-side or server ER_EMPTY_QUERY).
 *
 * @see self::MYSQL_ER_EMPTY_QUERY
 */
class EmptyQueryException extends DatabaseQueryException
{
    /**
     * Query was empty.
     *
     * {@link https://dev.mysql.com/doc/mysql-errors/8.0/en/server-error-reference.html#error_er_empty_query}
     */
    public const MYSQL_ER_EMPTY_QUERY = 1065;
}
