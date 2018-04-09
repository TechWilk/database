<?php
declare(strict_types=1);

namespace TechWilk\Database;

use TechWilk\Database\DatabaseResultInterface;

abstract class AbstractDatabaseResult implements DatabaseResultInterface
{
    /**
     * Fetches next row
     *
     * @see self::fetchObject()
     */
    public function fetch(string $className = 'stdClass', array $params = [])
    {
        return $this->fetchObject($className, $params);
    }

    /**
     * Fetches all rows
     */
    public function fetchAll(string $className = 'stdClass', array $params = []): array
    {
        return $this->fetchAllObject($className, $params);
    }

    /**
     * Fetches all rows as an array
     */
    public function fetchAllObject(string $className = 'stdClass', array $params = []): array
    {
        $data = [];
        for ($i=0; $i < $this->rowCount(); $i++) {
            $data[] = $this->fetchObject($className, $params);
        }

        return $data;
    }

    /**
     * Fetches all rows as an array
     */
    public function fetchAllArray(): array
    {
        $data = [];
        for ($i=0; $i < $this->rowCount(); $i++) {
            $data[] = $this->fetchArray();
        }

        return $data;
    }

    // ---- aliases for people used to the "get" syntax ----

    /**
     * Alias for fetch()
     * @see self::fetch()
     */
    public function get()
    {
        return $this->fetch();
    }

    /**
     * Alias for fetchAll()
     * @see self::fetchAll()
     */
    public function getAll(string $className = 'stdClass', array $params = []): array
    {
        return $this->fetchAll($className, $params);
    }

    /**
     * Alias for fetchAllObject()
     * @see self::fetchAllObject()
     */
    public function getObject(string $className = 'stdClass', array $params = [])
    {
        return $this->fetchAllObject($className, $params);
    }

    /**
     * Alias for fetchAllArray()
     * @see self::fetchAllArray()
     */
    public function getArray(): array
    {
        return $this->fetchAllArray();
    }

    /**
     * Alias for fetchColumn()
     * @see self::fetchColumn()
     */
    public function getColumn(string $column)
    {
        return $this->fetchColumn($column);
    }
}
