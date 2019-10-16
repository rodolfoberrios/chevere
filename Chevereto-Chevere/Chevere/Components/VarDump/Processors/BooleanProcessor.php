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

namespace Chevere\Components\VarDump\Processors;

use Chevere\Components\VarDump\Processors\Traits\ProcessorTrait;
use Chevere\Contracts\VarDump\ProcessorContract;

final class BooleanProcessor implements ProcessorContract
{
    use ProcessorTrait;

    public function __construct(bool $expression)
    {
        $this->val = $expression ? 'TRUE' : 'FALSE';
        $this->parentheses = '';
    }
}