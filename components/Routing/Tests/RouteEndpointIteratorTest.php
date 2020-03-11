<?php

/*
 * This file is part of Chevere.
 *
 * (c) Rodolfo Berrios <rodolfo@chevere.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Chevere\Components\Routing\Tests;

use Chevere\Components\Filesystem\Dir;
use Chevere\Components\Filesystem\Path;
use Chevere\Components\Route\Interfaces\RouteEndpointInterface;
use Chevere\Components\Routing\Exceptions\ExpectingRouteDecoratorException;
use Chevere\Components\Routing\RouteEndpointIterator;
use PHPUnit\Framework\TestCase;

final class RouteEndpointIteratorTest extends TestCase
{
    public function testObjects(): void
    {
        $dir = new Dir(new Path(__DIR__ . '/_resources/routes/articles/{id}/'));
        $endpointIterator = new RouteEndpointIterator($dir);
        $objectStorage = $endpointIterator->objects();
        $this->assertCount(1, $objectStorage);
        $objectStorage->rewind();
        while ($objectStorage->valid()) {
            $this->assertInstanceOf(
                RouteEndpointInterface::class,
                $objectStorage->current()
            );
            $objectStorage->next();
        }
    }

    public function testWrongObjects(): void
    {
        $dir = new Dir(new Path(__DIR__ . '/_resources/wrong_routes/articles/'));
        $this->expectException(ExpectingRouteDecoratorException::class);
        new RouteEndpointIterator($dir);
    }
}