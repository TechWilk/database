<?php

declare(strict_types=1);

namespace TechWilk\Database\MySqli;

use TechWilk\Database\AbstractDatabaseResult;
use TechWilk\Database\DatabaseResultInterface;
use TechWilk\Database\Exception\DatabaseException;

class MySqliDatabaseResult extends AbstractDatabaseResult implements DatabaseResultInterface
{
    protected $stmt;
    protected $result;

    /**
     * MySqliDatabaseResult constructor.
     */
    public function __construct(\mysqli_stmt $stmt)
    {
        $this->stmt = $stmt;
        $this->result = $stmt->get_result();
    }

    /**
     * Fetches next row as an object.
     *
     * @param string $className
     * @param array  $params to pass to mysqli_result::fetch_object()
     *
     * @throws DatabaseException
     */
    public function fetchObject(string $className = 'stdClass', array $params = [])
    {
        $this->checkResult('Cannot call fetchObject without a result');

        if ('stdClass' === $className) {
            return $this->result->fetch_object() ?? false;
        }

        return $this->result->fetch_object($className, $params) ?? false;
    }

    /**
     * Fetches next row as an array.
     */
    public function fetchArray($type = MYSQLI_ASSOC)
    {
        $this->checkResult('Cannot call fetchArray without a result');

        return $this->result->fetch_array($type) ?? false;
    }

    /**
     * Fetch Column.
     *
     * Fetches data from a single column in the result set.
     * Will only return NOT NULL values
     *
     * @throws DatabaseException
     */
    public function fetchColumn(string $column)
    {
        $this->checkResult('Cannot call fetchColumn without a result');

        $row = $this->result->fetch_assoc();

        if (null === $row) {
            return false;
        }

        if (!isset($row[$column])) {
            throw new DatabaseException('Column not found');
        }

        return $row[$column];
    }

    /**
     * Checks if the result is empty.
     */
    public function isEmpty(): bool
    {
        return 0 === $this->rowCount();
    }

    /**
     * @deprecated use isEmpty() instead
     */
    public function is_empty(): bool
    {
        return $this->isEmpty();
    }

    /**
     * Number of affected/fetched rows.
     *
     * @return int $rowCount
     */
    public function rowCount(): int
    {
        return $this->stmt->affected_rows;
    }

    /**
     * Resets the pointer for data seeking.
     */
    public function reset()
    {
        // TODO Need see if this is still wanted for mysli
        throw new DatabaseException('Function not available');
    }

    public function getSql()
    {
        // TODO this is not available in my sqli, not used anywhere
        throw new DatabaseException('Function not available');
    }

    /**
     * Checks if we have a result.
     *
     * @throws DatabaseException
     */
    private function checkResult(string $message)
    {
        if (!$this->result) {
            throw new DatabaseException($message);
        }
    }

    public function __destruct()
    {
        if ($this->result) {
            $this->result->free_result();
        }

        $this->stmt->free_result();
    }
}
