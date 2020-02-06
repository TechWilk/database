<?php

declare(strict_types=1);

namespace TechWilk\Database;

final class Query
{
    private $sql;
    private $parameters;

    public function __construct(string $sql, array $parameters = [])
    {
        $this->sql = $sql;
        $this->parameters = $parameters;
    }

    public static function fromSegments(array $querySegments): self
    {
        $sql = '';
        $params = [];

        foreach ($querySegments as $segment) {
            $sql .= ' ' . $segment->getSql();

            foreach ($segment->getParameters() as $param) {
                $params[] = $param;
            }
        }

        return new self(
            $sql,
            $params
        );
    }

    public function getSql(): string
    {
        return $this->sql;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function withSegment(QuerySegment $segment): self
    {
        $query = clone $this;
        $query->sql .= ' ' . $segment->getSql();
        $query->parameters += $segment->getParameters();

        return $query;
    }

    public function __set(string $property, mixed $value)
    {
        throw new \BadMethodCallException('Object is immutable.');
    }

    public function __unset(string $property)
    {
        throw new \BadMethodCallException('Object is immutable.');
    }
}
