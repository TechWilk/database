<?php

declare(strict_types=1);

namespace TechWilk\Database\Tests;

use PHPUnit\Framework\TestCase;
use TechWilk\Database\Query;
use TechWilk\Database\QuerySegment;

class QueryTest extends TestCase
{
    public function testWithSegmentAppendsParametersWithOverlappingNumericKeys(): void
    {
        $query = new Query('SELECT * FROM `table` WHERE id = ?', [1]);
        $segment = new QuerySegment('AND name = ?', ['my name']);

        $merged = $query->withSegment($segment);

        $this->assertSame(
            'SELECT * FROM `table` WHERE id = ? AND name = ?',
            $merged->getSql()
        );
        $this->assertSame([1, 'my name'], $merged->getParameters());
    }

    public function testWithSegmentAppendsMultipleParameters(): void
    {
        $query = new Query('SELECT * FROM `table` WHERE id = ?', [1]);
        $segment = new QuerySegment('AND id IN (?,?)', [2, 3]);

        $merged = $query->withSegment($segment);

        $this->assertSame([1, 2, 3], $merged->getParameters());
    }

    public function testWithSegmentDoesNotMutateOriginal(): void
    {
        $query = new Query('SELECT * FROM `table` WHERE id = ?', [1]);
        $segment = new QuerySegment('AND name = ?', ['my name']);

        $query->withSegment($segment);

        $this->assertSame('SELECT * FROM `table` WHERE id = ?', $query->getSql());
        $this->assertSame([1], $query->getParameters());
    }

    public function testFromSegmentsMergesParametersInOrder(): void
    {
        $query = Query::fromSegments([
            new QuerySegment('SELECT * FROM `table` WHERE id = ?', [1]),
            new QuerySegment('AND name = ?', ['my name']),
            new QuerySegment('AND active = ?', [true]),
        ]);

        $this->assertSame(
            ' SELECT * FROM `table` WHERE id = ? AND name = ? AND active = ?',
            $query->getSql()
        );
        $this->assertSame([1, 'my name', true], $query->getParameters());
    }
}
