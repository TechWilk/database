<?php

declare(strict_types=1);

namespace TechWilk\Database;

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
        $query->parameters = array_merge(
            $query->parameters,
            $segment->getParameters()
        );

        return $query;
    }

    public static function combine(self ...$segments): self
    {
        if (1 === count($segments)) {
            return reset($segments);
        }

        $accumulator = array_shift($segments);

        foreach ($segments as $segment) {
            $accumulator = $accumulator->withSegment($segment);
        }

        return $accumulator;
    }

    public static function combineWithSeparator(string $separator, self ...$segments): self
    {
        if (1 === count($segments)) {
            return reset($segments);
        }

        // We don't want to put $separator after the last segment
        $lastSegment = array_pop($segments);

        $segmentsWithSeparators = array_map(
            function (QuerySegment $segment) use ($separator) {
                return $segment->withSegment(new QuerySegment($separator));
            },
            $segments
        );

        $segmentsWithSeparators[] = $lastSegment;

        return QuerySegment::combine(...$segmentsWithSeparators);
    }

    public static function fieldIn(string $field, array $values, string $tablePrefix = ''): self
    {
        $sql = empty($tablePrefix) ? '' : '`' . $tablePrefix . '`.';
        $sql .= '`' . $field . '` IN (';
        $sql .= implode(',', array_fill(0, count($values), '?'));
        $sql .= ')';

        $values = array_values($values);

        return new self($sql, $values);
    }

    public function __set(string $property, $value)
    {
        throw new \BadMethodCallException('Object is immutable.');
    }

    public function __unset(string $property)
    {
        throw new \BadMethodCallException('Object is immutable.');
    }
}
