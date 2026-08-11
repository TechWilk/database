<?php

declare(strict_types=1);

namespace TechWilk\Database\Exception;

/**
 * Thrown when a CHECK constraint is violated.
 *
 * @see self::MYSQL_ER_CHECK_CONSTRAINT_VIOLATED
 */
class CheckConstraintException extends IntegrityConstraintException
{
    /**
     * Check constraint is violated.
     *
     * {@link https://dev.mysql.com/doc/mysql-errors/8.0/en/server-error-reference.html#error_er_check_constraint_violated}
     */
    public const MYSQL_ER_CHECK_CONSTRAINT_VIOLATED = 3819;
}
