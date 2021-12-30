<?php

declare(strict_types=1);

namespace TechWilk\Database;

use TechWilk\Database\Exception\BadFieldException;

trait MySqlSecureTableField
{
    /**
     * Returns table or field name surrounded by ` character.
     */
    protected function secureTableField(string $field): string
    {
        if (empty($field)) {
            throw new BadFieldException('Field name contains no characters');
        }

        if (false !== strpos($field, '`')) {
            throw new BadFieldException('Field name must not include ` character');
        }

        return '`' . $field . '`';
    }
}
