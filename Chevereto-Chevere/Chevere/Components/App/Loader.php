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

use Chevere\Components\Api\Api;
use Chevere\Components\App\Exceptions\NeedsToBeBuiltException;
use Chevere\Components\ArrayFile\ArrayFile;
use Chevere\Components\Cache\Cache;
use Chevere\Components\Cache\Exceptions\CacheNotFoundException;
use Chevere\Components\Console\Console;
use Chevere\Components\Http\Response;
use Chevere\Components\Message\Message;
use Chevere\Components\Path\PathHandle;
use Chevere\Components\Router\Router;
use Chevere\Contracts\App\BuildContract;
use Chevere\Contracts\App\BuilderContract;
use Chevere\Contracts\App\LoaderContract;
use Chevere\Contracts\App\ParametersContract;

use function console;

use const Chevere\CLI;
use const Chevere\DEV;

final class Loader implements LoaderContract
{
    /** @var BuilderContract */
    private $builder;

    /** @var ParametersContract */
    private $parameters;

    public function __construct()
    {
        $this->builder = new Builder(new App(new Response()));
        // $this->handleConsoleBind();
        $this->assert();
        $this->handleParameters();
        $build = $this->getBuild();
        $this->builder = $this->builder
            ->withBuild($build);
        $this->builder = $this->builder
            ->withApp(
                $this->builder->app()
                    ->withRouter($this->builder->build()->container()->router())
            );
    }

    public function run(): void
    {
        $this->builder->run();
    }

    private function handleParameters(): void
    {
        if (DEV || (CLI && console()->isBuilding())) {
            $this->parameters = new Parameters(
                new ArrayFile(
                    (new PathHandle(App::FILEHANDLE_PARAMETERS))->path()
                )
            );
            $this->builder = $this->builder
                ->withParameters($this->parameters);
        }
    }

    private function getBuild(): BuildContract
    {
        if (DEV) {
            return $this->builder->build()
                ->withParameters($this->parameters);
        }
        $api = new Api();
        $router = new Router();
        try {
            if (!(CLI && console()->isBuilding())) {
                $api = $api
                    ->withCache(new Cache('api'));
                $router = $router
                    ->withCache(new Cache('router'));
            }
            $container = $this->builder->build()->container()
                ->withApi($api)
                ->withRouter($router);
        } catch (CacheNotFoundException $e) {
            $message = (new Message('The app must be re-build due to missing cache: %message%'))
                ->code('%message%', $e->getMessage())
                ->toString();
            throw new NeedsToBeBuiltException($message, $e->getCode(), $e);
        }
        return $this->builder->build()
            ->withContainer($container);
    }

    // private function handleConsoleBind(): void
    // {
    //     if (CLI) {
    //         console()->bind($this->builder);
    //     }
    // }

    private function assert(): void
    {
        if (
            !DEV
            && !(CLI && console()->isBuilding())
            && !$this->builder->build()->file()->exists()
        ) {
            throw new NeedsToBeBuiltException(
                (new Message('The application needs to be built by CLI %command% or calling %method% method'))
                    ->code('%command%', 'php app/console build')
                    ->code('%method%', __CLASS__ . '::build')
                    ->toString()
            );
        }
    }
}