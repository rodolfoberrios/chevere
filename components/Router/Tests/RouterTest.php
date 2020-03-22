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

namespace Chevere\Components\Router\Tests;

use Chevere\Components\Http\Methods\GetMethod;
use Chevere\Components\Regex\Regex;
use Chevere\Components\Route\Route;
use Chevere\Components\Route\RouteEndpoint;
use Chevere\Components\Route\RouteName;
use Chevere\Components\Route\RoutePath;
use Chevere\Components\Router\Exceptions\RouteNotFoundException;
use Chevere\Components\Router\Exceptions\RouterException;
use Chevere\Components\Router\Routeable;
use Chevere\Components\Router\Routeables;
use Chevere\Components\Router\Router;
use Chevere\Components\Router\RouterGroups;
use Chevere\Components\Router\RouterIndex;
use Chevere\Components\Router\RouterRegex;
use Chevere\TestApp\App\Controllers\TestController;
use GuzzleHttp\Psr7\Uri;
use PHPUnit\Framework\TestCase;
use SplObjectStorage;

final class RouterTest extends TestCase
{
    private CacheHelper $cacheHelper;

    public function setUp(): void
    {
        $this->cacheHelper = new CacheHelper(__DIR__);
    }

    public function tearDown(): void
    {
        $this->cacheHelper->tearDown();
    }

    public function testConstructor(): void
    {
        $router = new Router();
        $this->assertFalse($router->hasRegex());
        $this->assertFalse($router->hasIndex());
        $this->assertFalse($router->hasGroups());
        $this->assertFalse($router->canResolve());
    }

    public function testUnableToResolveException(): void
    {
        $router = new Router();
        $this->expectException(RouterException::class);
        $router->resolve(new Uri('/'));
    }

    public function testRegexNotFound(): void
    {
        $regex = new RouterRegex(new Regex('#^(?|/test (*:0))$#x'));
        $router = (new Router())->withRegex($regex);
        $this->assertTrue($router->hasRegex());
        $this->assertSame($regex, $router->regex());
        $this->assertTrue($router->canResolve());
        $this->expectException(RouteNotFoundException::class);
        $router->resolve(new Uri('/not-found'));
    }

    public function testIndex(): void
    {
        $route = new Route(new RouteName('test-name'), new RoutePath('/test'));
        $route = $route->withAddedEndpoint(
            new RouteEndpoint(
                new GetMethod,
                new TestController
            )
        );
        $index = (new RouterIndex)->withAdded($route, 'test-group');
        $router = (new Router)->withIndex($index);
        $this->assertTrue($router->hasIndex());
        $this->assertSame($index, $router->index());
    }

    public function testGroups(): void
    {
        $groups = (new RouterGroups)->withAdded('test-group', 'test-name');
        $router = (new Router)->withGroups($groups);
        $this->assertTrue($router->hasGroups());
        $this->assertSame($groups, $router->groups());
    }

    public function testRouteables(): void
    {
        $route = new Route(new RouteName('some-name'), new RoutePath('/test'));
        $route = $route->withAddedEndpoint(
            new RouteEndpoint(
                new GetMethod,
                new TestController
            )
        );
        $routeable = new Routeable($route);
        $routeables = (new Routeables)->withPut($routeable);
        $router = (new Router)->withRouteables($routeables);
        $this->assertCount(1, $router->routeables()->map());
        $this->assertTrue($router->routeables()->hasKey($route->name()->toString()));
    }
}
