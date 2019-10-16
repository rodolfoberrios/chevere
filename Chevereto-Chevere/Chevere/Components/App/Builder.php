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

namespace Chevere\Components\App;

use LogicException;
use RuntimeException;

use Chevere\Components\App\Traits\ParametersAccessTrait;
use Chevere\Components\Controller\Traits\ControllerNameAccessTrait;
use Chevere\Components\Http\ServerRequest;
use Chevere\Components\Message\Message;
use Chevere\Components\Router\Exception\RouteNotFoundException;
use Chevere\Components\Runtime\Runtime;
use Chevere\Contracts\App\AppContract;
use Chevere\Contracts\App\BuildContract;
use Chevere\Contracts\App\BuilderContract;
use Chevere\Contracts\App\ParametersContract;
use Chevere\Contracts\Controller\JsonApiContract;
use Chevere\Contracts\Http\RequestContract;

use function console;
use function GuzzleHttp\Psr7\stream_for;

use const Chevere\CLI;

final class Builder implements BuilderContract
{
    use ParametersAccessTrait;
    use ControllerNameAccessTrait;

    /** @var AppContract */
    private $app;

    /** @var BuildContract */
    private $build;

    /** @var Runtime */
    private static $runtime;

    /** @var RequestContract */
    private static $request;

    /** @var bool True if run() has been called */
    private $ran;

    /** @var bool True if the console loop ran */
    private $consoleLoop;

    /** @var array */
    private $controllerArguments;

    public function __construct(AppContract $app)
    {
        $this->app = $app;
        $this->build = new Build($this);
    }

    public function withApp(AppContract $app): BuilderContract
    {
        $new = clone $this;
        $new->app = $app;

        return $new;
    }

    public function withParameters(ParametersContract $parameters): BuilderContract
    {
        $new = clone $this;
        $new->parameters = $parameters;

        return $new;
    }

    public function withBuild(BuildContract $build): BuilderContract
    {
        $new = clone $this;
        $new->build = $build;

        return $new;
    }

    public function withRequest(RequestContract $request): BuilderContract
    {
        $new = clone $this;
        $new::$request = $request;

        return $new;
    }

    public function withControllerName(string $controllerName): BuilderContract
    {
        $new = clone $this;
        $new->controllerName = $controllerName;

        return $new;
    }

    public function withControllerArguments(array $controllerArguments): BuilderContract
    {
        $new = clone $this;
        $new->controllerArguments = $controllerArguments;

        return $new;
    }

    public function hasRequest(): bool
    {
        return isset($this::$request);
    }

    public function hasControllerArguments(): bool
    {
        return isset($this->controllerArguments);
    }

    public function app(): AppContract
    {
        return $this->app;
    }

    public function build(): BuildContract
    {
        return $this->build;
    }

    /**
     * {@inheritdoc}
     */
    public function run(): void
    {
        $this->handleConsole();
        $this->handleRequest();
        if (isset($this->ran)) {
            throw new LogicException(
                (new Message('The method %method% has been already called'))
                    ->code('%method%', __METHOD__)
                    ->toString()
            );
        }
        $this->ran = true;
        if (!isset($this->controllerName)) {
            $this->processResolveCallable($this::$request->getUri()->getPath());
        }
        if (!isset($this->controllerName)) {
            throw new RuntimeException('DESCONTROL');
        }
        $this->runApp($this->controllerName);
    }

    public static function runtimeInstance(): Runtime
    {
        return self::$runtime;
    }

    /**
     * {@inheritdoc}
     */
    public static function requestInstance(): RequestContract
    {
        return self::$request;
    }

    /**
     * {@inheritdoc}
     */
    public static function setRuntimeInstance(Runtime $runtime)
    {
        self::$runtime = $runtime;
    }

    private function handleConsole(): void
    {
        if (CLI && !isset($this->consoleLoop)) {
            $this->consoleLoop = true;
            console()->bind($this);
            console()->run();
        }
    }

    private function handleRequest(): void
    {
        if (!isset($this::$request)) {
            $this::$request = ServerRequest::fromGlobals();
        }
    }

    private function processResolveCallable(string $pathInfo): void
    {
        try {
            $route = $this->app->router()->resolve($pathInfo);
        } catch (RouteNotFoundException $e) {
            $response = $this->app->response();
            $guzzle = $response->guzzle()->withStatus(404)->withBody(stream_for('Not found.'));
            $response = $response->withGuzzle($guzzle);
            $this->app = $this->app
                ->withResponse($response);
            if (CLI) {
                throw new RouteNotFoundException($e->getMessage());
            }
            $this->app->response()->sendHeaders()->sendBody();
            die();
        }
        $this->controllerName = $route->getController($this::$request->getMethod());
        $this->app = $this->app
            ->withRoute($route);
        $routerArgs = $this->app->router()->arguments();
        if (!isset($this->controllerArguments) && isset($routerArgs)) {
            $this->controllerArguments = $routerArgs;
        }
    }

    private function runApp(string $controller): void
    {
        $this->app = $this->app
            ->withArguments($this->controllerArguments);
        $runner = new Runner($this->app);
        $controller = $runner
            ->withControllerName($controller)
            ->run();
        $contentStream = stream_for($controller->content());
        $response = $this->app->response();
        $guzzle = $response->guzzle();
        $response = $response->withGuzzle(
            $controller instanceof JsonApiContract
                ? $guzzle->withJsonApi($contentStream)
                : $guzzle->withBody($contentStream)
        );
        $this->app = $this->app
            ->withResponse($response);
        if (!CLI) {
            $this->app->response()
                ->sendHeaders()
                ->sendBody();
        }
    }
}