<?php
declare(strict_types=1);

namespace TechWilk\Database\Pdo;

use TechWilk\Database\{
    AbstractDatabaseResult,
    DatabaseResultInterface
};
use PDO;
use PDOStatement;
use TechWilk\Database\Exception\DatabaseException;

class PdoDatabaseResult extends AbstractDatabaseResult implements DatabaseResultInterface
{
    protected $stmt;

    public function __construct(PDOStatement $stmt)
    {
        $this->stmt = $stmt;
    }

    /**
     * Fetches next row as an object
     */
    public function fetchObject(string $className = 'stdClass', array $params = [])
    {
        if ('stdClass' === $className) {
            return $this->stmt->fetch(PDO::FETCH_OBJ);
        }

        $this->stmt->setFetchMode(PDO::FETCH_CLASS, $className, $params);

        return $this->stmt->fetch(PDO::FETCH_CLASS);
    }

    /**
     * Fetches next row as an array
     */
    public function fetchArray()
    {
        $array = $this->stmt->fetch(PDO::FETCH_ASSOC);

        if (false === $array) {

            return null;
        }

        return $array;
    }

    /**
     * Fetch Column
     *
     * Fetches data from a single column in the result set.
     * Will only return NOT NULL values
     */
    public function fetchColumn(string $column)
    {
        $row = $this->fetchArray();

        if (!isset($row[$column])) {
            throw new DatabaseException('Column not found');
        }

        return $row[$column];
    }

    /**
     * Checks if the result is empty
     */
    public function isEmpty(): bool
    {
        return $this->rowCount() === 0;

    }

    /**
     * Number of affected/fetched rows
     *
     * @return int $rowCount
     */
    public function rowCount(): int
    {
        return $this->stmt->rowCount();
    }

    /**
     * Resets the pointer for data seeking
     */
    public function reset()
    {
        throw new DatabaseException('Function not available in PDO Database');
    }

    public function getSql()
    {
        return $this->stmt->queryString;
    }
}
