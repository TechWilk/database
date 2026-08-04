<?php

declare(strict_types=1);

namespace TechWilk\Database\Tests;

use TechWilk\Database\Exception\DatabaseDeadlockException;
use TechWilk\Database\Exception\DuplicateDatabaseRecordException;
use TechWilk\Database\Exception\EmptyQueryException;
use TechWilk\Database\Exception\InvalidTableException;

trait DatabaseServerExceptionTestsTrait
{
    public function testDatabaseDeadlockException(): void
    {
        $this->markTestSkipped('this is quite difficult to simulate');
        $this->expectException(DatabaseDeadlockException::class);
    }

    public function testDuplicateDatabaseRecordException(): void
    {
        $this->expectException(DuplicateDatabaseRecordException::class);

        $data = [
            'id' => 1,
            'date' => '2024-07-06 00:00:00',
            'string' => 'this should already exist, so should fail',
        ];
        $this->database->insert('table', $data);
    }

    public function testNoSuchTableException(): void
    {
        $this->expectException(InvalidTableException::class);

        $data = [
            'id' => 1,
            'date' => '2024-07-06 00:00:00',
            'string' => 'this should already exist, so should fail',
        ];
        $this->database->insert('invalid-table', $data);
    }

    public function testEmptyQueryException(): void
    {
        $this->expectException(EmptyQueryException::class);

        $this->database->query('');
    }

    public function testEmptyQueryExceptionWithWhitespaceOnly(): void
    {
        $this->expectException(EmptyQueryException::class);

        $this->database->query(' ');
    }
}
