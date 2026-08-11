<?php

declare(strict_types=1);

namespace TechWilk\Database\Exception;

/**
 * Thrown when a foreign key constraint fails on insert, update, or delete.
 *
 * @see self::MYSQL_ER_NO_REFERENCED_ROW
 * @see self::MYSQL_ER_ROW_IS_REFERENCED
 * @see self::MYSQL_ER_ROW_IS_REFERENCED_2
 * @see self::MYSQL_ER_NO_REFERENCED_ROW_2
 */
class ForeignKeyConstraintException extends IntegrityConstraintException
{
    /**
     * Cannot add or update a child row: a foreign key constraint fails.
     *
     * {@link https://dev.mysql.com/doc/mysql-errors/8.0/en/server-error-reference.html#error_er_no_referenced_row}
     */
    public const MYSQL_ER_NO_REFERENCED_ROW = 1216;

    /**
     * Cannot delete or update a parent row: a foreign key constraint fails.
     *
     * {@link https://dev.mysql.com/doc/mysql-errors/8.0/en/server-error-reference.html#error_er_row_is_referenced}
     */
    public const MYSQL_ER_ROW_IS_REFERENCED = 1217;

    /**
     * Cannot delete or update a parent row: a foreign key constraint fails (with detail).
     *
     * {@link https://dev.mysql.com/doc/mysql-errors/8.0/en/server-error-reference.html#error_er_row_is_referenced_2}
     */
    public const MYSQL_ER_ROW_IS_REFERENCED_2 = 1451;

    /**
     * Cannot add or update a child row: a foreign key constraint fails (with detail).
     *
     * {@link https://dev.mysql.com/doc/mysql-errors/8.0/en/server-error-reference.html#error_er_no_referenced_row_2}
     */
    public const MYSQL_ER_NO_REFERENCED_ROW_2 = 1452;
}
