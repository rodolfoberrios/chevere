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

namespace Chevere\Interfaces\Spec;

use Chevere\Interfaces\DataStructures\DsMapInterface;
use Chevere\Interfaces\Spec\SpecMethodsInterface;
use Generator;

interface SpecIndexMapInterface extends DsMapInterface
{
    /**
     * @return Generator<string, SpecMethodsInterface>
     */
    public function getGenerator(): Generator;

    public function withPut(string $name, SpecMethodsInterface $specMethods): SpecIndexMapInterface;

    public function hasKey(string $name): bool;

    public function get(string $name): SpecMethodsInterface;
}
