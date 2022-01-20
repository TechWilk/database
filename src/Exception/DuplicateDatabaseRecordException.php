<?php

declare(strict_types=1);

namespace TechWilk\Database\Exception;

class DuplicateDatabaseRecordException extends DatabaseException
{
    public function __construct($message, $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
