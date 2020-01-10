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

namespace Chevere\Components\Http\Contracts;

use Chevere\Components\Common\Contracts\ToArrayContract;
use Chevere\Components\Http\Exceptions\MethodNotFoundException;

interface MethodControllerNameCollectionContract extends ToArrayContract
{
    public function __construct(MethodControllerNameContract ...$methodControllerName);

    /**
     * Return an instance with the specified MethodControllerNameContract.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified MethodControllerNameContract.
     */
    public function withAddedMethodControllerName(MethodControllerNameContract $methodControllerName): MethodControllerNameCollectionContract;

    /**
     * Returns a boolean indicating whether the instance has any MethodContract.
     */
    public function hasAny(): bool;

    /**
     * Returns a boolean indicating whether the instance has the given MethodContract.
     */
    public function has(MethodContract $method): bool;

    /**
     * @throws MethodNotFoundException
     */
    public function get(MethodContract $method): MethodControllerNameContract;

    /**
     * @return array MethodControllerNameContract[]
     */
    public function toArray(): array;
}