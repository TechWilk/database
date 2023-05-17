<?php

declare(strict_types=1);

namespace TechWilk\Database\Tests;

use PHPUnit\Framework\TestCase;
use TechWilk\Database\Exception\BadFieldException;
use TechWilk\Database\Exception\DatabaseException;
use TechWilk\Database\ParseDataArray;

class ParseDataArrayTest extends TestCase
{
    use ParseDataArray;

    public function providerTestDataArray(): array
    {
        return [
            'simpleParams' => [
                [
                    'id' => 1,
                    'name' => 'test entry',
                    'date' => '2000-01-01',
                    'valid' => true,
                    'nothingHere' => null,
                ],
                '`id` = ?, `name` = ?, `date` = ?, `valid` = ?, `nothingHere` = ?',
                [
                    1,
                    'test entry',
                    '2000-01-01',
                    true,
                    null,
                ],
            ],
            'like match' => [
                [
                    'name LIKE' => '%test%',
                ],
                '`name` LIKE ?',
                [
                    '%test%',
                ],
            ],
            'is null match' => [
                [
                    'nothingHere IS' => 'unused',
                ],
                '`nothingHere` IS NULL',
                [],
            ],
            'is not match' => [
                [
                    'nothingHere IS NOT' => 'unused',
                ],
                '`nothingHere` IS NOT NULL',
                [],
            ],
            'field in single match' => [
                [
                    'id IN' => [5],
                ],
                '`id` IN (?)',
                [
                    5,
                ],
            ],
            'field in multiple match' => [
                [
                    'id IN' => [5, 6, 7],
                ],
                '`id` IN (?,?,?)',
                [
                    5,
                    6,
                    7,
                ],
            ],
            'greater than match' => [
                [
                    'id >' => 5,
                ],
                '`id` > ?',
                [
                    5,
                ],
            ],
            'greater than or equal match' => [
                [
                    'id >=' => 5,
                ],
                '`id` >= ?',
                [
                    5,
                ],
            ],
            'less than match' => [
                [
                    'id <' => 5,
                ],
                '`id` < ?',
                [
                    5,
                ],
            ],
            'less than or equal match' => [
                [
                    'id <=' => 5,
                ],
                '`id` <= ?',
                [
                    5,
                ],
            ],
            'not equal match' => [
                [
                    'id !=' => 5,
                ],
                '`id` != ?',
                [
                    5,
                ],
            ],
            'addition by' => [
                [
                    'id +' => 5,
                ],
                '`id` = `id` + ?',
                [
                    5,
                ],
            ],
            'more complex multiple parameters' => [
                [
                    'id +' => 5,
                ],
                '`id` = `id` + ?',
                [
                    5,
                ],
            ],
        ];
    }

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

    /**
     * @dataProvider providerTestDataArray
     */
    public function testDataArray(
        array $data,
        string $expectedSql,
        array $expectedParameters
    ): void {
        $queryStatement = $this->parseDataArray($data);

        $this->assertEquals($expectedSql, $queryStatement->getSql());
        $this->assertEquals($expectedParameters, $queryStatement->getParameters());
    }

    public function testDataArrayFailsWithNoData(): void
    {
        $this->expectException(DatabaseException::class);

        $this->parseDataArray([]);
    }

    public function testDataArrayFailsWithInvalidFieldInExpression(): void
    {
        $this->expectException(DatabaseException::class);

        $this->parseDataArray(['id IN' => 'not an array']);
    }
}
