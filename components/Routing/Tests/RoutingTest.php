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

use Chevere\Components\Cache\Cache;
use Chevere\Components\Filesystem\Dir;
use Chevere\Components\Filesystem\Interfaces\Dir\DirInterface;
use Chevere\Components\Filesystem\Path;
use Chevere\Components\Router\Interfaces\RouterInterface;
use Chevere\Components\Router\RouterCache;
use Chevere\Components\Router\RouterMaker;
use Chevere\Components\Routing\RoutePathIterator;
use Chevere\Components\Routing\Routing;
use PHPUnit\Framework\TestCase;

final class RoutingTest extends TestCase
{
    private DirInterface $resourcesDir;

    private DirInterface $cacheDir;

    private DirInterface $routesDir;

    public function setUp(): void
    {
        $this->resourcesDir = new Dir(new Path(__DIR__ . '/_resources/'));
        $this->cacheDir = $this->resourcesDir->getChild('cache/');
        $this->routesDir = $this->resourcesDir->getChild('routes/');
        if (!$this->cacheDir->exists()) {
            $this->cacheDir->create(0777);
        }
    }

    public function tearDown(): void
    {
        $this->cacheDir->removeContents();
    }

    public function testConstruct(): void
    {
        $routePathIterator = new RoutePathIterator($this->routesDir);
        $routerCache = new RouterCache(new Cache($this->cacheDir));
        $routerMaker = new RouterMaker($routerCache);
        $routing = new Routing($routePathIterator, $routerMaker);
        $this->assertInstanceOf(RouterInterface::class, $routing->routeMaker()->router());
    }
}