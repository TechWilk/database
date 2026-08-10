<?php

declare(strict_types=1);

namespace TechWilk\Database\Pdo;

use stdClass;
use TechWilk\Database\AbstractDatabaseResult;
use TechWilk\Database\ArrayFetchType;
use TechWilk\Database\DatabaseResultInterface;
use TechWilk\Database\Exception\DatabaseException;

class PdoDatabaseResult extends AbstractDatabaseResult implements DatabaseResultInterface
{
    public function __construct(
        protected \PDOStatement $stmt
    ) {
    }

    /**
     * Fetches next row as an object.
     */
    public function fetchObject(string $className = \stdClass::class, array $params = []): ?stdClass
    {
        if (\stdClass::class === $className) {
            return $this->stmt->fetch(\PDO::FETCH_OBJ);
        }

        $this->stmt->setFetchMode(\PDO::FETCH_CLASS, $className, $params);

        return $this->stmt->fetch(\PDO::FETCH_CLASS);
    }

    /**
     * Fetches next row as an array.
     */
    public function fetchArray(ArrayFetchType $type = ArrayFetchType::ASSOC): ?array
    {
        $type = match ($type) {
            ArrayFetchType::ASSOC => \PDO::FETCH_ASSOC,
            ArrayFetchType::NUM => \PDO::FETCH_NUM,
            ArrayFetchType::BOTH => \PDO::FETCH_BOTH,
        };

        $array = $this->stmt->fetch($type);

        if (false === $array) {
            return null;
        }

        return $array;
    }

    /**
     * Fetch Column.
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
        return $this->stmt->rowCount();
    }

    /**
     * Resets the pointer for data seeking.
     */
    public function reset(): never
    {
        throw new DatabaseException('Function not available in PDO Database');
    }

    public function getSql(): string
    {
        return $this->stmt->queryString;
    }
}
