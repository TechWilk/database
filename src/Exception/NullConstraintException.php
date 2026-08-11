<?php

declare(strict_types=1);

namespace TechWilk\Database\Exception;

/**
 * Thrown when a NOT NULL column is given a NULL value.
 *
 * @see self::MYSQL_ER_BAD_NULL_ERROR
 */
class NullConstraintException extends IntegrityConstraintException
{
    /**
     * Column cannot be null.
     *
     * {@link https://dev.mysql.com/doc/mysql-errors/8.0/en/server-error-reference.html#error_er_bad_null_error}
     */
    public const MYSQL_ER_BAD_NULL_ERROR = 1048;
}
