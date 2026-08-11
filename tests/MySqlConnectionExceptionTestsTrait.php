<?php

declare(strict_types=1);

namespace TechWilk\Database\Tests;

use TechWilk\Database\DatabaseInterface;
use TechWilk\Database\Exception\DatabaseAccessDeniedException;
use TechWilk\Database\Exception\DatabaseConnectionException;
use TechWilk\Database\Exception\DatabaseObjectNotFoundException;

trait MySqlConnectionExceptionTestsTrait
{
    abstract protected function connectToMySql(
        string $host,
        string $database,
        string $username,
        string $password,
        int $port,
    ): DatabaseInterface;

    public function testDatabaseObjectNotFoundExceptionNoDatabaseSelected(): void
    {
        $this->expectException(DatabaseObjectNotFoundException::class);

        $database = $this->connectToMySql(
            (string) getenv('MYSQL_HOST'),
            '',
            (string) getenv('MYSQL_USER'),
            (string) getenv('MYSQL_PASSWORD'),
            (int) getenv('MYSQL_PORT'),
        );

        $database->query('SELECT * FROM `table`');
    }

    public function testDatabaseAccessDeniedException(): void
    {
        $this->expectException(DatabaseAccessDeniedException::class);

        $this->connectToMySql(
            (string) getenv('MYSQL_HOST'),
            (string) getenv('MYSQL_DATABASE'),
            (string) getenv('MYSQL_USER'),
            'definitely-wrong-password-' . bin2hex(random_bytes(4)),
            (int) getenv('MYSQL_PORT'),
        );
    }

    public function testDatabaseConnectionException(): void
    {
        $this->expectException(DatabaseConnectionException::class);

        set_error_handler(static fn (): bool => true);

        try {
            $this->connectToMySql(
                'nonexistent.invalid',
                (string) getenv('MYSQL_DATABASE'),
                (string) getenv('MYSQL_USER'),
                (string) getenv('MYSQL_PASSWORD'),
                (int) getenv('MYSQL_PORT'),
            );
        } finally {
            restore_error_handler();
        }
    }
}
