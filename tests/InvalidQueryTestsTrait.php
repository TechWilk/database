<?php

declare(strict_types=1);

namespace TechWilk\Database\Tests;

use TechWilk\Database\Exception\DatabaseException;

trait InvalidQueryTestsTrait
{
    public function testInvalidQueryWithoutParameters(): void
    {
        $this->expectException(DatabaseException::class);

        $this->database->query('this is not a sql query');
    }

    public function testInvalidSqlLookingQueryWithoutParameters(): void
    {
        $this->expectException(DatabaseException::class);

        $this->database->query('SELECT FROM `table`');
    }

    public function testInvalidSqlLookingQueryWithParameters(): void
    {
        $this->expectException(DatabaseException::class);

        $parameters = [1];
        $this->database->query('SELECT * FROM `table` WHERE', $parameters);
    }

    public function testInvalidTableInsert(): void
    {
        $this->expectException(DatabaseException::class);

        $data = [
            'id' => 3,
            'date' => '2022-03-03 00:00:00',
            'string' => 'third entry has been inserted',
        ];
        $this->database->insert('table-does-not-exist', $data);
    }

    public function testInvalidTableUpdate(): void
    {
        $this->expectException(DatabaseException::class);

        $data = [
            'string' => 'second entry has been updated',
        ];
        $this->database->update('table-does-not-exist', $data, ['id' => 2]);
    }

    public function testInvalidTableDelete(): void
    {
        $this->expectException(DatabaseException::class);

        $this->database->delete('table-does-not-exist', ['id' => 2]);
    }
}
