<?php

declare(strict_types=1);

namespace TechWilk\Database\MySqli;

class MySqliDatabaseFromConnection extends MySqliDatabase
{
    public function __construct(
        protected \mysqli $mysqli
    ) {
        if ($this->mysqli->connect_errno !== 0) {
            throw self::createExceptionFromMysqliError(
                (string) $this->mysqli->connect_error,
                $this->mysqli->connect_errno,
                $this->mysqli->sqlstate,
            );
        }

        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    }

    public function __destruct()
    {
        // We don't want to close this connection
    }
}
