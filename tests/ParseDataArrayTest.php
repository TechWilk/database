<?php

declare(strict_types=1);

namespace TechWilk\Database\Tests;

use PHPUnit\Framework\TestCase;
use TechWilk\Database\Exception\BadValueException;
use TechWilk\Database\Exception\DatabaseException;
use TechWilk\Database\MySqlSecureTableField;
use TechWilk\Database\ParseDataArray;

class ParseDataArrayTest extends TestCase
{
    use ParseDataArray;
    use MySqlSecureTableField;

    public static function providerTestDataArray(): \Iterator
    {
        yield 'simpleParams' => [
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
        ];
        yield 'like match' => [
            [
                'name LIKE' => '%test%',
            ],
            '`name` LIKE ?',
            [
                '%test%',
            ],
        ];
        yield 'is null match' => [
            [
                'nothingHere IS' => 'unused',
            ],
            '`nothingHere` IS NULL',
            [],
        ];
        yield 'is not match' => [
            [
                'nothingHere IS NOT' => 'unused',
            ],
            '`nothingHere` IS NOT NULL',
            [],
        ];
        yield 'field in single match' => [
            [
                'id IN' => [5],
            ],
            '`id` IN (?)',
            [
                5,
            ],
        ];
        yield 'field in multiple match' => [
            [
                'id IN' => [5, 6, 7],
            ],
            '`id` IN (?,?,?)',
            [
                5,
                6,
                7,
            ],
        ];
        yield 'greater than match' => [
            [
                'id >' => 5,
            ],
            '`id` > ?',
            [
                5,
            ],
        ];
        yield 'greater than or equal match' => [
            [
                'id >=' => 5,
            ],
            '`id` >= ?',
            [
                5,
            ],
        ];
        yield 'less than match' => [
            [
                'id <' => 5,
            ],
            '`id` < ?',
            [
                5,
            ],
        ];
        yield 'less than or equal match' => [
            [
                'id <=' => 5,
            ],
            '`id` <= ?',
            [
                5,
            ],
        ];
        yield 'not equal match' => [
            [
                'id !=' => 5,
            ],
            '`id` != ?',
            [
                5,
            ],
        ];
        yield 'addition by' => [
            [
                'id +' => 5,
            ],
            '`id` = `id` + ?',
            [
                5,
            ],
        ];
        yield 'more complex multiple parameters' => [
            [
                'id +' => 5,
            ],
            '`id` = `id` + ?',
            [
                5,
            ],
        ];
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

        $this->assertSame($expectedSql, $queryStatement->getSql());
        $this->assertEquals($expectedParameters, $queryStatement->getParameters());
    }

    public function testDataArrayFailsWithNoData(): void
    {
        $this->expectException(DatabaseException::class);

        $this->parseDataArray([]);
    }

    public function testDataArrayFailsWithInvalidFieldInExpression(): void
    {
        $this->expectException(BadValueException::class);

        $this->parseDataArray(['id IN' => 'not an array']);
    }

    public function testDataArrayFailsWithEmptyFieldInExpression(): void
    {
        $this->expectException(BadValueException::class);

        $this->parseDataArray(['id IN' => []]);
    }
}
