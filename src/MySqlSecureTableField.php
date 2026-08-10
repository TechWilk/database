<?php

declare(strict_types=1);

namespace TechWilk\Database;

use TechWilk\Database\Exception\BadFieldException;
use TechWilk\Database\Exception\DatabaseQueryException;

trait MySqlSecureTableField
{
    /**
     * Returns table or field name surrounded by ` character.
     */
    protected function secureTableField(string $field): string
    {
        if ($field === '') {
            throw new BadFieldException(
                'Field name contains no characters',
                DatabaseQueryException::SQLSTATE_SYNTAX_ERROR_OR_ACCESS_RULE_VIOLATION,
            );
        }

        if (false !== strpos($field, '`')) {
            throw new BadFieldException(
                'Field name must not include ` character',
                DatabaseQueryException::SQLSTATE_SYNTAX_ERROR_OR_ACCESS_RULE_VIOLATION,
            );
        }

        return '`' . $field . '`';
    }
}
