<?php

declare(strict_types=1);

namespace TechWilk\Database\Tests;

use TechWilk\Database\Exception\CheckConstraintException;
use TechWilk\Database\Exception\DatabaseDataException;
use TechWilk\Database\Exception\DatabaseDeadlockException;
use TechWilk\Database\Exception\DatabaseObjectExistsException;
use TechWilk\Database\Exception\DatabaseQueryException;
use TechWilk\Database\Exception\DatabaseReadOnlyException;
use TechWilk\Database\Exception\DuplicateDatabaseRecordException;
use TechWilk\Database\Exception\EmptyQueryException;
use TechWilk\Database\Exception\ForeignKeyConstraintException;
use TechWilk\Database\Exception\InvalidTableException;
use TechWilk\Database\Exception\NullConstraintException;

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

    public function testNullConstraintException(): void
    {
        $this->expectException(NullConstraintException::class);

        $this->database->query(
            'INSERT INTO `table` (`id`, `date`, `string`) VALUES (?, ?, ?)',
            [100, null, 'null date should fail']
        );
    }

    public function testCheckConstraintException(): void
    {
        $this->expectException(CheckConstraintException::class);

        $this->database->query(
            'INSERT INTO `table` (`id`, `date`, `string`) VALUES (?, ?, ?)',
            [100, '2024-07-06 00:00:00', '']
        );
    }

    public function testForeignKeyConstraintException(): void
    {
        $this->loadSeedFile('seeds-foreign-key.sql');

        $this->expectException(ForeignKeyConstraintException::class);

        $this->database->query(
            'INSERT INTO `child` (`id`, `parent_id`) VALUES (?, ?)',
            [2, 999]
        );
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

    public function testDatabaseObjectExistsException(): void
    {
        $this->expectException(DatabaseObjectExistsException::class);

        $this->database->query('CREATE TABLE `table` (`id` INT)');
    }

    public function testDatabaseQueryException(): void
    {
        $this->expectException(DatabaseQueryException::class);

        $this->database->query('SELEC FROM `table`');
    }

    public function testDatabaseDataException(): void
    {
        $this->expectException(DatabaseDataException::class);

        $this->database->query(
            'INSERT INTO `table` (`id`, `date`, `string`) VALUES (?, ?, ?)',
            [100, 'not-a-date', 'invalid date should fail']
        );
    }

    public function testDatabaseReadOnlyException(): void
    {
        $this->expectException(DatabaseReadOnlyException::class);

        $this->database->query('SET SESSION TRANSACTION READ ONLY');

        try {
            $this->database->query(
                'INSERT INTO `table` (`id`, `date`, `string`) VALUES (?, ?, ?)',
                [100, '2024-07-06 00:00:00', 'read only should fail']
            );
        } finally {
            try {
                $this->database->query('SET SESSION TRANSACTION READ WRITE');
            } catch (\Throwable) {
                // Ignore cleanup failures after the expected exception path.
            }
        }
    }
}
