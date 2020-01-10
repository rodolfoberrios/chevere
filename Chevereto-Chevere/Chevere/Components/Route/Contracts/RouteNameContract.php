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

namespace Chevere\Components\Route\Contracts;

use Chevere\Components\Route\Exceptions\RouteInvalidNameException;

interface RouteNameContract
{
    /** Regex pattern used to validate route name. */
    const REGEX = '/^[\w\-\.]+$/i';

    public function __construct(string $name);

    /**
     * @return string Route name.
     */
    public function toString(): string;
}