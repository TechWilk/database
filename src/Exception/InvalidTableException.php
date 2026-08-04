<?php

declare(strict_types=1);

namespace TechWilk\Database\Exception;

class InvalidTableException extends DatabaseException
{
    public function __construct($message, $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
