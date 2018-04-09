<?php
declare(strict_types=1);

namespace TechWilk\Database;

use BadMethodCallException;

final class QuerySegment
{
    private $sql;
    private $parameters;

    public function __construct(string $sql, array $parameters = [])
    {
        $this->sql = $sql;
        $this->parameters = $parameters;
    }

    public function getSql(): string
    {
        return $this->sql;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function withSql(string $sql): self
    {
        $query = clone $this;
        $query->sql = $sql;

        return $query;
    }

    public function withParameters(array $parameters): self
    {
        $query = clone $this;
        $query->parameters = $parameters;

        return $query;
    }

    public function withSegment(self $segment): self
    {
        $query = clone $this;
        $query->sql .= ' ' . $segment->getSql();
        $query->parameters += $segment->getParameters();

        return $query;
    }

    public static function fieldIn($field, $values)
    {
        $sql = '`' . $field . '` IN (';
        $sql .= implode(",", array_fill(0, count($values), '?'));
        $sql .= ')';

        $values = array_values($values);

        return new self($sql, $values);
    }

    public function __set(string $property, mixed $value)
    {
        throw new BadMethodCallException('Object is immutable.');
    }

    public function __unset(string $property)
    {
        throw new BadMethodCallException('Object is immutable.');
    }
}