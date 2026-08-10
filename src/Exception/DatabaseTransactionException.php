<?php

declare(strict_types=1);

namespace TechWilk\Database\Exception;

/**
 * Thrown when a statement is illegal for the current transaction state.
 *
 * This is a category parent. Available children are:
 *
 * @see DatabaseReadOnlyException
 *
 * SQLSTATE and driver specific error codes:
 *
 * @see self::SQLSTATE_CLASS
 */
class DatabaseTransactionException extends DatabaseException
{
    /**
     * SQLSTATE class: invalid transaction state.
     */
    public const SQLSTATE_CLASS = '25';
}
