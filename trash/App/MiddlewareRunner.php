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

namespace Chevere\Components\App;

use Chevere\Components\App\Exceptions\AppWithoutRequestException;
use Chevere\Components\App\Interfaces\AppInterface;
use Chevere\Components\App\Interfaces\MiddlewareRunnerInterface;
use Chevere\Components\Message\Message;
use Chevere\Exceptions\Middleware\MiddlewareNamesEmptyException;
use Chevere\Interfaces\Http\RequestInterface;
use Chevere\Interfaces\Middleware\MiddlewareNameInterface;
use Chevere\Interfaces\Middleware\MiddlewaresInterface;

final class MiddlewareRunner implements MiddlewareRunnerInterface
{
    private AppInterface $app;

    private MiddlewaresInterface $middlewareNameCollection;

    /** @var bool */
    private bool $hasRun;

    /** @var array An array containg the middlewares that have ran */
    private array $record;

    public function __construct(MiddlewaresInterface $middlewareNameCollection, AppInterface $app)
    {
        $this->app = $app;
        $this->assertAppWithRequest();
        $this->middlewareNameCollection = $middlewareNameCollection;
        $this->assertMiddlewareNamesNotEmpty();
        $this->hasRun = false;
    }

    public function withRun(): MiddlewareRunnerInterface
    {
        $new = clone $this;
        $new->doRun();
        $new->hasRun = true;

        return $new;
    }

    public function hasRun(): bool
    {
        return $this->hasRun;
    }

    public function record(): array
    {
        return $this->record;
    }

    private function doRun(): void
    {
        foreach ($this->middlewareNameCollection->toArray() as $middlewareName) {
            $middleware = $middlewareName->toString();
            (new $middleware())
                ->handle(
                    $this->app->request()
                );
            $this->record[] = $middleware;
        }
    }

    private function assertMiddlewareNamesNotEmpty(): void
    {
        if ($this->middlewareNameCollection->count() === 0) {
            throw new MiddlewareNamesEmptyException(
                (new Message("Instance of %className% doesn't contain any %contract% contract"))
                    ->code('%className%', MiddlewaresInterface::class)
                    ->code('%contract%', MiddlewareNameInterface::class)
                    ->toString()
            );
        }
    }

    private function assertAppWithRequest(): void
    {
        if (!$this->app->hasRequest()) {
            throw new AppWithoutRequestException(
                (new Message('Instance of %type% must contain a %contract% contract'))
                    ->code('%type%', AppInterface::class)
                    ->code('%contract%', RequestInterface::class)
                    ->toString()
            );
        }
    }
}
