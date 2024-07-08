<?php

declare(strict_types=1);

namespace TechWilk\Database\Tests;

use TechWilk\Database\Exception\DatabaseDeadlockException;
use TechWilk\Database\Exception\DuplicateDatabaseRecordException;

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
}
