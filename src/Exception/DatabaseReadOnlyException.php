<?php

declare(strict_types=1);

namespace TechWilk\Database\Exception;

/**
 * Thrown when a write is refused because the server or session is read-only.
 *
 * @see SQLSTATE_READ_ONLY_SQL_TRANSACTION
 * @see MYSQL_ER_READ_ONLY_TRANSACTION
 * @see MYSQL_ER_OPTION_PREVENTS_STATEMENT
 */
class DatabaseReadOnlyException extends DatabaseTransactionException
{
    /**
     * SQLSTATE: read-only SQL-transaction.
     */
    public const SQLSTATE_READ_ONLY_SQL_TRANSACTION = '25006';

    /**
     * Read only transaction cannot execute statement that attempts to modify table.
     *
     * {@link https://dev.mysql.com/doc/mysql-errors/8.0/en/server-error-reference.html#error_er_read_only_transaction}
     */
    public const MYSQL_ER_READ_ONLY_TRANSACTION = 1207;

    /**
     * The MySQL server is running with an option that prevents this statement
     * (commonly --read-only / super_read_only). SQLSTATE is often HY000; map by
     * errno. The message may name other options too.
     *
     * {@link https://dev.mysql.com/doc/mysql-errors/8.0/en/server-error-reference.html#error_er_option_prevents_statement}
     */
    public const MYSQL_ER_OPTION_PREVENTS_STATEMENT = 1290;
}
