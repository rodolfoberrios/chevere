<?php

declare(strict_types=1);
/*
 * This file is part of Chevereto\Core.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chevereto\Core\Controllers;

use const Chevereto\Core\CLI;
use Chevereto\Core\Console;
use Chevereto\Core\Message;
use Chevereto\Core\Controller;
use Chevereto\Core\Route;
use InvalidArgumentException;

/**
 * Identical to GET, but without any message-boby in the response.
 */
class ApiHead extends Controller
{
    const OPTIONS = [
        'description' => 'GET without message-body.',
    ];

    /** @var Route */
    private $route;

    public function __invoke(string $endpoint = null)
    {
        if (isset($endpoint)) {
            $route = $this->getApp()->getRouter()->resolve($endpoint);
        } else {
            if ($this->getApp()->hasRoute()) {
                $route = $this->getApp()->getRoute();
            } else {
                $message =
                    (string)
                        (new Message('Must provide the %s argument when running this callable without route context.'))
                            ->code('%s', '$endpoint');
                if (CLI) {
                    Console::io()->error($message);

                    return;
                } else {
                    throw new InvalidArgumentException($message);
                }
            }
        }

        if (!isset($route)) {
            $this->getResponse()->setStatusCode(404);

            return;
        }

        $this->route = $route;

        $this->process();
    }

    private function process()
    {
        $callable = $this->route->getCallable('GET');
        $controller = $this->getApp()->getControllerObject($callable);
        $controller->getResponse()->unsetData();
        if (CLI) {
            Console::io()->block($controller->getResponse()->getStatusString(), 'STATUS', 'fg=black;bg=green', ' ', true);
        }
    }
}
