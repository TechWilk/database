<?php

declare(strict_types=1);

namespace TechWilk\Database\Tests;

use PHPUnit\Framework\TestCase;
use TechWilk\Database\MySqli\MySqliDatabase;

class MySqliDatabaseTest extends TestCase
{
    use ValidQueryTestsTrait;
    use InvalidQueryTestsTrait;

    protected $database;

    public function setUp(): void
    {
        $this->database = new MySqliDatabase(
            getenv('MYSQL_HOST'),
            getenv('MYSQL_DATABASE'),
            getenv('MYSQL_USER'),
            getenv('MYSQL_PASSWORD'),
            MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT,
            false,
            (int) getenv('MYSQL_PORT'),
        );

        // reset the schema
        $sqlContent = file_get_contents(__DIR__ . '/data/seeds.sql');
        $sqlStatements = explode(';', $sqlContent);
        foreach ($sqlStatements as $sql) {
            if (0 === strlen(trim($sql))) {
                continue;
            }

            $this->database->query($sql);
        }
    }
}
