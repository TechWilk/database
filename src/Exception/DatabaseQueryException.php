<?php

declare(strict_types=1);

namespace TechWilk\Database\Exception;

/**
 * Thrown when a query is invalid or cannot be executed as written (syntax, shape, empty, etc.).
 *
 * This is a category parent. Available children are:
 *
 * @see BadFieldException
 * @see BadValueException
 * @see EmptyQueryException
 *
 * SQLSTATE and driver specific error codes:
 *
 * @see self::SQLSTATE_CLASS
 * @see self::SQLSTATE_CLASS_CARDINALITY
 * @see self::SQLSTATE_SYNTAX_ERROR_OR_ACCESS_RULE_VIOLATION
 * @see self::MYSQL_ER_NON_UNIQ_ERROR
 */
class DatabaseQueryException extends DatabaseException
{
    /**
     * SQLSTATE class: syntax error or access rule violation.
     */
    public const SQLSTATE_CLASS = '42';

    /**
     * SQLSTATE class: cardinality violation.
     */
    public const SQLSTATE_CLASS_CARDINALITY = '21';

    /**
     * Syntax error or access rule violation (no subclass).
     *
     * Residual unmapped 42000 errors fall back to this category.
     *
     * {@link https://dev.mysql.com/doc/mysql-errors/8.0/en/server-error-reference.html}
     */
    public const SQLSTATE_SYNTAX_ERROR_OR_ACCESS_RULE_VIOLATION = '42000';

    /**
     * Column is ambiguous.
     *
     * MySQL reports SQLSTATE 23000 for this errno - treat as query-shape, not integrity.
     *
     * {@link https://dev.mysql.com/doc/mysql-errors/8.0/en/server-error-reference.html#error_er_non_uniq_error}
     */
    public const MYSQL_ER_NON_UNIQ_ERROR = 1052;
}
