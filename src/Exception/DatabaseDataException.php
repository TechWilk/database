<?php

declare(strict_types=1);

namespace TechWilk\Database\Exception;

/**
 * Thrown when a value is rejected as invalid for the target column or type.
 *
 * This is a category parent. Available children are:
 * - none currently
 *
 * SQLSTATE and driver specific error codes:
 *
 * @see self::SQLSTATE_CLASS
 */
class DatabaseDataException extends DatabaseException
{
    /**
     * SQLSTATE class: data exception.
     */
    public const SQLSTATE_CLASS = '22';
}
