<?php

declare(strict_types=1);

namespace TechWilk\Database\Tests;

use PHPUnit\Framework\TestCase;
use TechWilk\Database\MySqli\MySqliDatabase;

class MySqliDatabaseTest extends TestCase
{
    use ValidQueryTestsTrait;
    use InvalidQueryTestsTrait;
    use DatabaseServerExceptionTestsTrait;

    protected $database;

    protected function setUp(): void
    {
        $this->database = MySqliDatabase::connect(
            host: getenv('MYSQL_HOST'),
            database: getenv('MYSQL_DATABASE'),
            username: getenv('MYSQL_USER'),
            password: getenv('MYSQL_PASSWORD'),
            errorReportingLevel: MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT,
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
