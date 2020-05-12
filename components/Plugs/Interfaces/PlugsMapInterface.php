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

namespace Chevere\Components\Plugs\Interfaces;

use Countable;
use Generator;

interface PlugsMapInterface extends Countable
{
    public function withAddedPlug(AssertPlugInterface $assertPlug): PlugsMapInterface;

    public function has(PlugInterface $plug): bool;

    public function hasPlugableName(string $plugableName): bool;

    /**
     * @return Generator<string , PlugsQueueInterface>
     */
    public function getGenerator(): Generator;
}
