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

namespace Chevere\Interfaces\Action;

use Chevere\Interfaces\Controller\ControllerInterface;

/**
 * Describes the component in charge of running the controller.
 */
interface ActionRunnerInterface
{
    public function __construct(ControllerInterface $controller);

    /**
     * Executes the controller with the given `$arguments`.
     */
    public function execute(array $arguments): ActionExecutedInterface;
}
