<?php

declare(strict_types=1);

namespace TechWilk\Database\Tests;

use TechWilk\Database\ArrayFetchType;

trait ValidQueryTestsTrait
{
    public function testSelectWithoutParameters(): void
    {
        $result = $this->database->query('SELECT * FROM `table`');

        $this->assertEquals(2, $result->rowCount());
        $this->assertEquals([
            [
                'id' => 1,
                'date' => '2020-01-01 00:00:00',
                'string' => 'this is some text',
            ],
            [
                'id' => 2,
                'date' => '2021-02-02 00:00:00',
                'string' => 'some more test text',
            ],
        ], $result->fetchAllArray());
    }

    public function testSelectWithParameters(): void
    {
        $parameters = [1];
        $result = $this->database->query('SELECT * FROM `table` WHERE id = ?', $parameters);

        $this->assertEquals(1, $result->rowCount());
        $this->assertEquals([
            [
                'id' => 1,
                'date' => '2020-01-01 00:00:00',
                'string' => 'this is some text',
            ],
        ], $result->fetchAllArray());
    }

    public function testSelectWithParametersAndFetchAllArrayAssoc(): void
    {
        $parameters = [1];
        $result = $this->database->query('SELECT * FROM `table` WHERE id = ?', $parameters);

        $this->assertEquals([
            [
                'id' => 1,
                'date' => '2020-01-01 00:00:00',
                'string' => 'this is some text',
            ],
        ], $result->fetchAllArray(ArrayFetchType::ASSOC));
    }

    public function testSelectWithParametersAndFetchAllArrayNum(): void
    {
        $parameters = [1];
        $result = $this->database->query('SELECT * FROM `table` WHERE id = ?', $parameters);

        $this->assertEquals([
            [
                1,
                '2020-01-01 00:00:00',
                'this is some text',
            ],
        ], $result->fetchAllArray(ArrayFetchType::NUM));
    }

    public function testSelectWithParametersAndFetchAllArrayBoth(): void
    {
        $parameters = [1];
        $result = $this->database->query('SELECT * FROM `table` WHERE id = ?', $parameters);

        $this->assertEquals([
            [
                0 => 1,
                'id' => 1,
                1 => '2020-01-01 00:00:00',
                'date' => '2020-01-01 00:00:00',
                2 => 'this is some text',
                'string' => 'this is some text',
            ],
        ], $result->fetchAllArray(ArrayFetchType::BOTH));
    }

    public function testInsert(): void
    {
        $data = [
            'id' => 3,
            'date' => '2022-03-03 00:00:00',
            'string' => 'third entry has been inserted',
        ];
        $result = $this->database->insert('table', $data);

        $this->assertEquals(3, $result);

        // confirm it is now in the table
        $parameters = [3];
        $result = $this->database->query('SELECT * FROM `table` WHERE id = ?', $parameters);

        $this->assertEquals(1, $result->rowCount());
        $this->assertEquals([$data], $result->fetchAllArray());
    }

    public function testUpdate(): void
    {
        $data = [
            'string' => 'second entry has been updated',
        ];
        $result = $this->database->update('table', $data, ['id' => 2]);

        // one row updated
        $this->assertEquals(1, $result);

        // confirm it is now in the table
        $parameters = [2];
        $result = $this->database->query('SELECT string FROM `table` WHERE id = ?', $parameters);

        $this->assertEquals(1, $result->rowCount());
        $this->assertEquals([$data], $result->fetchAllArray());
    }

    public function testDelete(): void
    {
        $result = $this->database->delete('table', ['id' => 2]);

        // one row updated
        $this->assertEquals(1, $result);

        // confirm it is now in the table
        $parameters = [2];
        $result = $this->database->query('SELECT string FROM `table` WHERE id = ?', $parameters);

        $this->assertEquals(0, $result->rowCount());
        $this->assertEquals([], $result->fetchAllArray());
    }
}
