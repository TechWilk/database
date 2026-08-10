<?php

declare(strict_types=1);

namespace TechWilk\Database\Exception;

/**
 * Thrown when a table or field name is invalid for quoting.
 *
 * @see DatabaseQueryException::SQLSTATE_SYNTAX_ERROR_OR_ACCESS_RULE_VIOLATION
 */
class BadFieldException extends DatabaseQueryException
{
}
