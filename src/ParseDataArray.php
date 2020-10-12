<?php

declare(strict_types=1);

namespace TechWilk\Database;

use TechWilk\Database\Exception\DatabaseException;

trait ParseDataArray
{
    // should be a constant (if they were allowed in traits)
    private $validEquators = [
        'LIKE',
        'IS',
        'IS NOT',
        'IN',
        '>',
        '>=',
        '<',
        '<=',
        '!=',

        // modifiers
        '+',
        '-',
    ];

    /**
     * Parse data array.
     *
     * @param string $glue for the implode()
     *                     use ', ' for SET clauses
     *                     or ' AND ' for WHERE clauses
     *
     * @throws DatabaseException
     */
    protected function parseDataArray(array $data, string $glue = ', '): QuerySegment
    {
        if (empty($data)) {
            throw new DatabaseException('No data');
        }

        $sqlSegments = [];
        $parameters = [];
        foreach ($data as $field => $value) {
            $equator = $this->parseEquatorFromField($field);

            // remove equator from field string
            if ('=' !== $equator) {
                $equatorWithSpace = ' ' . $equator;
                $startPosition = strlen($field) - strlen($equatorWithSpace);
                $field = substr($field, 0, $startPosition);
            }

            switch ($equator) {
                case 'IS':
                case 'IS NOT':
                    $sqlSegments[] = $this->secureTableField($field) . ' ' . $equator . ' NULL';
                    break;
                case 'IN':
                    if (!is_array($value)) {
                        throw new DatabaseException('Invalid value for SQL IN statement');
                    }
                    $placeholders = implode(',', array_fill(0, count($value), '?'));
                    $sqlSegments[] = $this->secureTableField($field) . ' ' . $equator . ' (' . $placeholders . ')';
                    $parameters = array_merge($parameters, $value);
                    break;
                case '+':
                case '-':
                    $sqlSegments[] = $this->secureTableField($field) . ' = ' . $this->secureTableField($field) . ' ' . $equator . ' ?';
                    $parameters[] = $value;
                    break;
                default:
                $sqlSegments[] = $this->secureTableField($field) . ' ' . $equator . ' ?';
                $parameters[] = $value;
            }

        }

        $sql = implode($glue, $sqlSegments);

        return new QuerySegment($sql, $parameters);
    }

    private function parseEquatorFromField(string $field): string
    {
        foreach ($this->validEquators as $equator) {
            $equatorWithSpace = ' ' . $equator;
            $startPosition = strlen($field) - strlen($equatorWithSpace);

            if ($startPosition < 0) {
                continue;
            }

            if (false !== strpos($field, $equatorWithSpace, $startPosition)) {

                return $equator;
            }
        }

        return '=';
    }
}
