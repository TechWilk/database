<?php

declare(strict_types=1);

namespace TechWilk\Database\Tests;

use PHPUnit\Framework\TestCase;
use TechWilk\Database\Pdo\PdoDatabase;

class PdoDatabaseTest extends TestCase
{
    use ValidQueryTestsTrait;
    use InvalidQueryTestsTrait;
    use DatabaseServerExceptionTestsTrait;

    protected $database;

    protected function setUp(): void
    {
        $this->database = new PdoDatabase(
            getenv('MYSQL_HOST'),
            getenv('MYSQL_DATABASE'),
            getenv('MYSQL_USER'),
            getenv('MYSQL_PASSWORD'),
            false,
            (int) getenv('MYSQL_PORT'),
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
