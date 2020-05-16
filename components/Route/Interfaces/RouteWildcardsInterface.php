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

namespace Chevere\Components\Route\Interfaces;

use Chevere\Components\Common\Interfaces\ToArrayInterface;

interface RouteWildcardsInterface extends ToArrayInterface
{
    public function __construct();

    /**
     * Return an instance with the specified WildcardInterface.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified WildcardInterface.
     */
    public function withAddedWildcard(RouteWildcardInterface $wildcard): RouteWildcardsInterface;

    /**
     * Returns a boolean indicating whether the instance has any WildcardInterface.
     */
    public function hasAny(): bool;

    /**
     * Returns a boolean indicating whether the instance has a given WildcardInterface.
     */
    public function has(RouteWildcardInterface $wildcard): bool;

    /**
     * Provides access to the target WildcardInterface instance.
     */
    public function get(RouteWildcardInterface $wildcard): RouteWildcardInterface;

    /**
     * Returns a boolean indicating whether the instance has WildcardInterface in the given pos.
     */
    public function hasPos(int $pos): bool;

    /**
     * Provides access to the target WildcardInterface instance in the given pos.
     */
    public function getPos(int $pos): RouteWildcardInterface;
}
