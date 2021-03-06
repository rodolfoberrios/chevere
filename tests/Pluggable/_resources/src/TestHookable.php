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

namespace Chevere\Tests\Pluggable\_resources\src;

use Chevere\Components\Pluggable\PluggableAnchors;
use Chevere\Components\Pluggable\Plug\Hook\Traits\PluggableHooksTrait;
use Chevere\Interfaces\Pluggable\PluggableAnchorsInterface;
use Chevere\Interfaces\Pluggable\Plug\Hook\PluggableHooksInterface;

class TestHookable implements PluggableHooksInterface
{
    use PluggableHooksTrait;

    private string $string;

    public function __construct()
    {
        $string = '';
        $this->hook('hook-anchor-1', $string);

        $this->string = $string;
    }

    public static function getHookAnchors(): PluggableAnchorsInterface
    {
        return new PluggableAnchors(
            'hook-anchor-1',
            'hook-anchor-2'
        );
    }

    public function setString(string $string): void
    {
        $this->string = $string;
        $this->hook('hook-anchor-2', $string);
        $this->string = $string;
    }

    public function string(): string
    {
        return $this->string;
    }
}
