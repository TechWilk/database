<?php

declare(strict_types=1);

namespace TechWilk\Database\MySqli;

use TechWilk\Database\Exception\DatabaseException;

class MySqliDatabaseFromConnection extends MySqliDatabase
{
    protected $mysqli;

    public function __construct(
        \mysqli $connection
    ) {
        $this->mysqli = $connection;

        if ($this->mysqli->connect_errno) {
            throw new DatabaseException('Failed to connect to MySQL: (' . $this->mysqli->connect_errno . ') ' . $this->mysqli->connect_error, $this->mysqli->connect_errno);
        }

        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    }

    public function __destruct()
    {
        // We don't want to close this connection
    }
}
