<?php

declare(strict_types=1);

namespace TechWilk\Database\Exception;

/**
 * Thrown when a CHECK constraint is violated.
 *
 * @see self::MYSQL_ER_CHECK_CONSTRAINT_VIOLATED
 * @see self::MARIADB_ER_CONSTRAINT_FAILED
 */
class CheckConstraintException extends IntegrityConstraintException
{
    /**
     * Check constraint is violated.
     *
     * {@link https://dev.mysql.com/doc/mysql-errors/8.0/en/server-error-reference.html#error_er_check_constraint_violated}
     */
    public const MYSQL_ER_CHECK_CONSTRAINT_VIOLATED = 3819;

    /**
     * CONSTRAINT x failed (MariaDB CHECK / constraint enforcement).
     *
     * {@link https://mariadb.com/docs/server/reference/error-codes/mariadb-error-codes-4000-to-4099/e4025}
     */
    public const MARIADB_ER_CONSTRAINT_FAILED = 4025;
}
