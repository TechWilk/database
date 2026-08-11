<?php

declare(strict_types=1);

namespace TechWilk\Database\Tests;

use PHPUnit\Framework\TestCase;
use TechWilk\Database\DatabaseInterface;
use TechWilk\Database\Pdo\PdoDatabase;

class PdoMySqlDatabaseTest extends TestCase
{
    use ValidQueryTestsTrait;
    use InvalidQueryTestsTrait;
    use DatabaseServerExceptionTestsTrait;
    use MySqlConnectionExceptionTestsTrait;
    use LoadsSqlSeeds;

    protected $database;

    protected function connectToMySql(
        string $host,
        string $database,
        string $username,
        string $password,
        int $port,
    ): DatabaseInterface {
        return PdoDatabase::connectToMySql(
            host: $host,
            database: $database,
            username: $username,
            password: $password,
            usePersistentConnection: false,
            port: $port,
        );
    }

    protected function setUp(): void
    {
        $this->database = $this->connectToMySql(
            (string) getenv('MYSQL_HOST'),
            (string) getenv('MYSQL_DATABASE'),
            (string) getenv('MYSQL_USER'),
            (string) getenv('MYSQL_PASSWORD'),
            (int) getenv('MYSQL_PORT'),
        );

        // reset the schema
        $this->loadSeedFile('seeds.sql');
    }
}
