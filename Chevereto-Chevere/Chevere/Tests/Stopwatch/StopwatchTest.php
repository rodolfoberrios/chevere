<?php

/*
 * This file is part of Chevere.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Chevere\Tests\Stopwatch;

use BadMethodCallException;
use Chevere\Components\Stopwatch\Stopwatch;
use PHPUnit\Framework\TestCase;
use function ChevereFn\stringEndsWith;

final class StopwatchTest extends TestCase
{
    public function testConstruct(): void
    {
        $this->expectNotToPerformAssertions();
        (new Stopwatch());
    }

    public function testBadRecordsCall(): void
    {
        $this->expectException(BadMethodCallException::class);
        (new Stopwatch())
            ->records();
    }

    public function testBadRecordsReadCall(): void
    {
        $this->expectException(BadMethodCallException::class);
        (new Stopwatch())
            ->recordsRead();
    }

    public function testBadTimeElapsedCall(): void
    {
        $this->expectException(BadMethodCallException::class);
        (new Stopwatch())
            ->timeElapsed();
    }

    public function testBadTimeElapsedReadCall(): void
    {
        $this->expectException(BadMethodCallException::class);
        (new Stopwatch())
            ->timeElapsedRead();
    }

    public function testStopwatch(): void
    {
        $sw = new Stopwatch();
        $nanoTime = 100000; // 100000 = 0.1 ms
        $marks = 2;
        for ($i = 0; $i < $marks; $i++) {
            time_nanosleep(0, 100000);
            $sw->mark((string) $i);
        }
        time_nanosleep(0, 100000);
        $sw->stop();
        $recordsSum = array_sum(array_values($sw->records()));
        $this->assertSame($recordsSum, $sw->timeElapsed());
        $this->assertTrue($recordsSum > $nanoTime * ($marks + 2));
        $this->assertTrue(stringEndsWith(' ms', $sw->timeElapsedRead()));
    }
}