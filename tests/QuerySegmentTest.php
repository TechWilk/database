<?php

declare(strict_types=1);

namespace TechWilk\Database\Tests;

use PHPUnit\Framework\TestCase;
use TechWilk\Database\Exception\BadValueException;
use TechWilk\Database\QuerySegment;

class QuerySegmentTest extends TestCase
{
    public function testWithSegmentAppendsParametersWithOverlappingNumericKeys(): void
    {
        $segment = new QuerySegment('id = ?', [1]);
        $other = new QuerySegment('AND name = ?', ['my name']);

        $merged = $segment->withSegment($other);

        $this->assertSame('id = ? AND name = ?', $merged->getSql());
        $this->assertSame([1, 'my name'], $merged->getParameters());
    }

    public function testWithSegmentAppendsMultipleParameters(): void
    {
        $segment = new QuerySegment('id = ?', [1]);
        $other = new QuerySegment('AND id IN (?,?)', [2, 3]);

        $merged = $segment->withSegment($other);

        $this->assertSame([1, 2, 3], $merged->getParameters());
    }

    public function testWithSegmentDoesNotMutateOriginal(): void
    {
        $segment = new QuerySegment('id = ?', [1]);
        $other = new QuerySegment('AND name = ?', ['my name']);

        $segment->withSegment($other);

        $this->assertSame('id = ?', $segment->getSql());
        $this->assertSame([1], $segment->getParameters());
    }

    public function testCombineMergesParametersInOrder(): void
    {
        $combined = QuerySegment::combine(
            new QuerySegment('id = ?', [1]),
            new QuerySegment('AND name = ?', ['my name']),
            new QuerySegment('AND active = ?', [true])
        );

        $this->assertSame('id = ? AND name = ? AND active = ?', $combined->getSql());
        $this->assertSame([1, 'my name', true], $combined->getParameters());
    }

    public function testCombineWithSeparatorMergesParametersInOrder(): void
    {
        $combined = QuerySegment::combineWithSeparator(
            'AND',
            new QuerySegment('id = ?', [1]),
            new QuerySegment('name = ?', ['my name']),
            new QuerySegment('active = ?', [true])
        );

        $this->assertSame('id = ? AND name = ? AND active = ?', $combined->getSql());
        $this->assertSame([1, 'my name', true], $combined->getParameters());
    }

    public function testFieldInBuildsPlaceholdersAndParameters(): void
    {
        $segment = QuerySegment::fieldIn('id', [5, 6, 7]);

        $this->assertSame('`id` IN (?,?,?)', $segment->getSql());
        $this->assertSame([5, 6, 7], $segment->getParameters());
    }

    public function testFieldInWithEmptyValuesThrows(): void
    {
        $this->expectException(BadValueException::class);
        $this->expectExceptionMessage('Invalid value for SQL IN statement');

        QuerySegment::fieldIn('id', []);
    }
}
