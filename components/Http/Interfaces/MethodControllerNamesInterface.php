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

namespace Chevere\Components\Http\Interfaces;

use Chevere\Components\Http\Exceptions\MethodNotFoundException;
use Chevere\Components\Http\MethodControllerNameObjectsRead;

interface MethodControllerNamesInterface
{
    public function __construct(MethodControllerNameInterface ...$methodControllerName);

    /**
     * Return an instance with the specified added MethodControllerNameInterface.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified MethodControllerNameInterface.
     *
     * Note: This method overrides any method already added.
     */
    public function withAddedMethodControllerName(MethodControllerNameInterface $methodControllerName): MethodControllerNamesInterface;

    /**
     * Returns a boolean indicating whether the instance has any MethodInterface.
     */
    public function hasAny(): bool;

    /**
     * Returns a boolean indicating whether the instance has the given MethodInterface.
     */
    public function hasMethod(MethodInterface $method): bool;

    /**
     * @throws MethodNotFoundException
     */
    public function getMethod(MethodInterface $method): MethodControllerNameInterface;

    public function objects(): MethodControllerNameObjectsRead;
}
