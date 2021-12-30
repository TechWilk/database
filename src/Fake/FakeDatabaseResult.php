<?php

declare(strict_types=1);

namespace TechWilk\Database\Fake;

use TechWilk\Database\AbstractDatabaseResult;
use TechWilk\Database\DatabaseResultInterface;
use TechWilk\Database\Exception\DatabaseException;

class FakeDatabaseResult extends AbstractDatabaseResult implements DatabaseResultInterface
{
    private $returnData;

    public function __construct(array $returnData = null)
    {
        $this->returnData = $returnData;
    }

    /**
     * Fetches next row as an object.
     */
    public function fetchObject(string $className = 'stdClass', array $params = [])
    {
        return (object) $this->returnData;
    }

    /**
     * Fetches next row as an array.
     */
    public function fetchArray()
    {
        return $this->returnData;
    }

    /**
     * Fetch Column.
     *
     * Fetches data from a single column in the result set.
     * Will only return NOT NULL values
     */
    public function fetchColumn(string $column)
    {
        if (!isset($this->returnData[$column])) {
            throw new DatabaseException('Column not found');
        }

        return $this->returnData[$column];
    }

    /**
     * Checks if the result is empty.
     */
    public function isEmpty(): bool
    {
        return 0 === $this->rowCount();
    }

    /**
     * Number of affected/fetched rows.
     *
     * @return int $rowCount
     */
    public function rowCount(): int
    {
        return count($this->returnData);
    }

    /**
     * Resets the pointer for data seeking.
     */
    public function reset()
    {
        throw new DatabaseException('Function not available');
    }

    public function getSql()
    {
        throw new DatabaseException('Function not available');
    }
}
