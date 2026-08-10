<?php

declare(strict_types=1);

namespace TechWilk\Database\Exception;

class DatabaseException extends \Exception
{
    private ?string $sqlState = null;

    public function __construct($message, $sqlState = null, $code = 0, \Exception $previous = null)
    {
        $this->sqlState = $sqlState;
        parent::__construct($message, $code, $previous);
    }

    public function getSqlState(): ?string
    {
        return $this->sqlState;
    }
}
