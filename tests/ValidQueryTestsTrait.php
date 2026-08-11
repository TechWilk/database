<?php

declare(strict_types=1);

namespace TechWilk\Database\Tests;

use TechWilk\Database\ArrayFetchType;
use TechWilk\Database\Query;

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

    public function testRunQuery(): void
    {
        $query = new Query('SELECT * FROM `table` WHERE id = ?', [1]);
        $result = $this->database->runQuery($query);

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

    public function testInsertOnDuplicate(): void
    {
        $this->database->insertOnDuplicate(
            'table',
            [
                'id' => 1,
                'date' => '2024-07-06 00:00:00',
                'string' => 'original string value',
            ],
            [
                'string' => 'updated via duplicate key',
            ],
        );

        $result = $this->database->query('SELECT string FROM `table` WHERE id = ?', [1]);

        $this->assertEquals(1, $result->rowCount());
        $this->assertEquals([
            ['string' => 'updated via duplicate key'],
        ], $result->fetchAllArray());
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

    public function testUpdateUsingIn(): void
    {
        $data = [
            'string' => 'updated via in()',
        ];
        $result = $this->database->updateUsingIn('table', $data, ['id' => [1, 2]]);

        $this->assertEquals(2, $result);

        $result = $this->database->query('SELECT string FROM `table` ORDER BY id');

        $this->assertEquals([
            ['string' => 'updated via in()'],
            ['string' => 'updated via in()'],
        ], $result->fetchAllArray());
    }

    public function testUpdateChangesWithUnchangedDataReturnsZero(): void
    {
        $result = $this->database->updateChanges(
            'table',
            [
                'string' => 'this is some text',
            ],
            ['id' => 1],
        );

        $this->assertEquals(0, $result);
    }

    public function testUpdateChangesWithChangedData(): void
    {
        $data = [
            'string' => 'only changed fields updated',
        ];
        $result = $this->database->updateChanges('table', $data, ['id' => 1]);

        $this->assertEquals(1, $result);

        $result = $this->database->query('SELECT string FROM `table` WHERE id = ?', [1]);

        $this->assertEquals([$data], $result->fetchAllArray());
    }

    public function testInsertOrUpdateUpdatesExistingRow(): void
    {
        $data = [
            'id' => 1,
            'date' => '2020-01-01 00:00:00',
            'string' => 'updated via insertOrUpdate',
        ];
        $result = $this->database->insertOrUpdate('table', $data, ['id' => 1]);

        $this->assertEquals(1, $result);

        $result = $this->database->query('SELECT * FROM `table` WHERE id = ?', [1]);

        $this->assertEquals([$data], $result->fetchAllArray());
    }

    public function testInsertOrUpdateInsertsMissingRow(): void
    {
        $data = [
            'id' => 3,
            'date' => '2022-03-03 00:00:00',
            'string' => 'inserted via insertOrUpdate',
        ];
        $result = $this->database->insertOrUpdate('table', $data, ['id' => 3]);

        $this->assertEquals(3, $result);

        $result = $this->database->query('SELECT * FROM `table` WHERE id = ?', [3]);

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

    public function testDeleteUsingIn(): void
    {
        $result = $this->database->deleteUsingIn('table', ['id' => [1, 2]]);

        $this->assertEquals(2, $result);

        $result = $this->database->query('SELECT * FROM `table`');

        $this->assertEquals(0, $result->rowCount());
        $this->assertEquals([], $result->fetchAllArray());
    }
}
