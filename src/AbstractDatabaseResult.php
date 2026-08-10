<?php

declare(strict_types=1);

namespace TechWilk\Database;

use stdClass;

abstract class AbstractDatabaseResult implements DatabaseResultInterface
{
    /**
     * Fetches next row.
     *
     * @see self::fetchObject()
     */
    public function fetch(string $className = \stdClass::class, array $params = []): ?stdClass
    {
        return $this->fetchObject($className, $params);
    }

    /**
     * Fetches all rows.
     */
    public function fetchAll(string $className = \stdClass::class, array $params = []): array
    {
        return $this->fetchAllObject($className, $params);
    }

    /**
     * Fetches all rows as an array.
     */
    public function fetchAllObject(string $className = \stdClass::class, array $params = []): array
    {
        $data = [];
        for ($i = 0; $i < $this->rowCount(); ++$i) {
            $data[] = $this->fetchObject($className, $params);
        }

        return $data;
    }

    /**
     * Fetches all rows as an array.
     */
    public function fetchAllArray(ArrayFetchType $type = ArrayFetchType::ASSOC): array
    {
        $data = [];
        for ($i = 0; $i < $this->rowCount(); ++$i) {
            $data[] = $this->fetchArray($type);
        }

        return $data;
    }

    // ---- aliases for people used to the "get" syntax ----

    /**
     * Alias for fetch().
     *
     * @see self::fetch()
     */
    public function get(): ?stdClass
    {
        return $this->fetch();
    }

    /**
     * Alias for fetchAll().
     *
     * @see self::fetchAll()
     */
    public function getAll(string $className = \stdClass::class, array $params = []): array
    {
        return $this->fetchAll($className, $params);
    }

    /**
     * Alias for fetchObject().
     *
     * @see self::fetchObject()
     */
    public function getObject(string $className = \stdClass::class, array $params = []): ?stdClass
    {
        return $this->fetchObject($className, $params);
    }

    /**
     * Alias for fetchArray().
     *
     * @see self::fetchArray()
     */
    public function getArray(ArrayFetchType $type = ArrayFetchType::ASSOC): ?array
    {
        return $this->fetchArray($type);
    }

    /**
     * Alias for fetchColumn().
     *
     * @see self::fetchColumn()
     */
    public function getColumn(string $column)
    {
        return $this->fetchColumn($column);
    }
}
