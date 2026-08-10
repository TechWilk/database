<?php

declare(strict_types=1);

namespace TechWilk\Database\Exception;

/**
 * Thrown when establishing or retaining a database connection fails.
 *
 * This is a category parent. Available children are:
 * - none currently
 *
 * SQLSTATE and driver specific error codes:
 *
 * @see self::SQLSTATE_CLASS
 * @see self::MYSQL_CR_CONNECTION_ERROR
 * @see self::MYSQL_CR_CONN_HOST_ERROR
 * @see self::MYSQL_CR_UNKNOWN_HOST
 * @see self::MYSQL_CR_SERVER_GONE_ERROR
 * @see self::MYSQL_CR_SERVER_LOST
 */
class DatabaseConnectionException extends DatabaseException
{
    /**
     * SQLSTATE class: connection exception.
     */
    public const SQLSTATE_CLASS = '08';

    /**
     * Can't connect to local MySQL server through socket / named pipe.
     *
     * {@link https://dev.mysql.com/doc/mysql-errors/8.0/en/client-error-reference.html#error_cr_connection_error}
     */
    public const MYSQL_CR_CONNECTION_ERROR = 2002;

    /**
     * Can't connect to MySQL server on host.
     *
     * {@link https://dev.mysql.com/doc/mysql-errors/8.0/en/client-error-reference.html#error_cr_conn_host_error}
     */
    public const MYSQL_CR_CONN_HOST_ERROR = 2003;

    /**
     * Unknown MySQL server host.
     *
     * {@link https://dev.mysql.com/doc/mysql-errors/8.0/en/client-error-reference.html#error_cr_unknown_host}
     */
    public const MYSQL_CR_UNKNOWN_HOST = 2005;

    /**
     * MySQL server has gone away.
     *
     * {@link https://dev.mysql.com/doc/mysql-errors/8.0/en/client-error-reference.html#error_cr_server_gone_error}
     */
    public const MYSQL_CR_SERVER_GONE_ERROR = 2006;

    /**
     * Lost connection to MySQL server during query.
     *
     * {@link https://dev.mysql.com/doc/mysql-errors/8.0/en/client-error-reference.html#error_cr_server_lost}
     */
    public const MYSQL_CR_SERVER_LOST = 2013;
}
