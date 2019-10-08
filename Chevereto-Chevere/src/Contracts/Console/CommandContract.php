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

namespace Chevere\Contracts\Console;

use Chevere\Console\Console;
use Chevere\Contracts\App\BuilderContract;

interface CommandContract
{
    public function __construct(Console $console);

    public function console(): Console;

    public function symfony(): SymfonyCommandContract;

    public function callback(BuilderContract $builder): int;
}
