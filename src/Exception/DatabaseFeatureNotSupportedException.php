<?php

declare(strict_types=1);

namespace TechWilk\Database\Exception;

/**
 * Thrown when the server or driver does not support the requested feature or statement.
 *
 * This is a category parent. Available children are:
 * - none currently
 *
 * SQLSTATE and driver specific error codes:
 *
 * @see self::SQLSTATE_FEATURE_NOT_SUPPORTED
 */
class DatabaseFeatureNotSupportedException extends DatabaseException
{
    /**
     * Feature not supported.
     *
     * {@link https://dev.mysql.com/doc/refman/8.0/en/error-message-elements.html}
     */
    public const SQLSTATE_FEATURE_NOT_SUPPORTED = '0A000';
}
