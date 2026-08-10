<?php

declare(strict_types=1);

namespace TechWilk\Database\Tests;

use PHPUnit\Framework\TestCase;
use TechWilk\Database\Pdo\PdoDatabase;

class PdoMySqlDatabaseTest extends TestCase
{
    use ValidQueryTestsTrait;
    use InvalidQueryTestsTrait;
    use DatabaseServerExceptionTestsTrait;

    protected $database;

    protected function setUp(): void
    {
        $this->database = PdoDatabase::connectToMySql(
            host: getenv('MYSQL_HOST'),
            database: getenv('MYSQL_DATABASE'),
            username: getenv('MYSQL_USER'),
            password: getenv('MYSQL_PASSWORD'),
            usePersistentConnection: false,
            port: (int) getenv('MYSQL_PORT'),
        );

        // reset the schema
        $sqlContent = file_get_contents(__DIR__ . '/data/seeds.sql');
        $sqlStatements = explode(';', $sqlContent);
        foreach ($sqlStatements as $sql) {
            if (trim($sql) === '') {
                continue;
            }

            $this->database->query($sql);
        }
    }
}
