<?php

declare(strict_types=1);

namespace TechWilk\Database\Exception;

/**
 * Thrown when building a query with an invalid value (e.g. empty IN list).
 *
 * @see DatabaseQueryException::SQLSTATE_SYNTAX_ERROR_OR_ACCESS_RULE_VIOLATION
 */
class BadValueException extends DatabaseQueryException
{
}
