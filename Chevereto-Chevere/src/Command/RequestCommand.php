<?php

declare(strict_types=1);
/*
 * This file is part of Chevere.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chevereto\Chevere\Command;

use Chevereto\Chevere\App;
use Chevereto\Chevere\HttpRequest;
use Chevereto\Chevere\Command;
use ReflectionMethod;

// TODO: Check php app/console request /api

/**
 * The RequestCommand allows to pass a forged request to the App instance.
 *
 * Usage:
 * php app/console request <method> <uri>
 */
class RequestCommand extends Command
{
    protected static $defaultName = 'request';

    protected function configure()
    {
        $this
            ->setDescription('Forge and resolve a HTTP request')
            ->setHelp('This command allows you to forge a HTTP request')
            ->addArgument('method', Command::ARGUMENT_OPTIONAL, 'HTTP request method', 'GET')
            ->addArgument('uri', Command::ARGUMENT_OPTIONAL, 'URI', '/')
            ->addArgument('parameters', Command::ARGUMENT_OPTIONAL, 'Parameters', [])
            ->addArgument('cookies', Command::ARGUMENT_OPTIONAL, 'Cookies', [])
            ->addArgument('files', Command::ARGUMENT_OPTIONAL, 'Files', [])
            ->addArgument('server', Command::ARGUMENT_OPTIONAL, 'Server', [])
            ->addArgument('content', Command::ARGUMENT_OPTIONAL, 'Content', null);
    }

    /**
     * Forge a request.
     */
    public function callback(App $app): int
    {
        // Map cli arguments to Request::create
        $arguments = $this->getCli()->getInput()->getArguments();
        $requestArguments = [];
        $r = new ReflectionMethod(HttpRequest::class, 'create');
        foreach ($r->getParameters() as $requestArg) {
            $requestArguments[] = $arguments[$requestArg->getName()] ?? $requestArg->getDefaultValue() ?? null;
        }
        $app->forgeHttpRequest(...$requestArguments)->run();

        return 1;
    }
}
