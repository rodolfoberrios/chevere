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

namespace Chevere\Components\Bootstrap;

use Chevere\Exceptions\Filesystem\DirNotExistsException;
use Chevere\Exceptions\Filesystem\DirNotWritableException;
use Chevere\Interfaces\Bootstrap\BootstrapInterface;
use Chevere\Interfaces\Filesystem\DirInterface;
use Throwable;

final class Bootstrap implements BootstrapInterface
{
    private int $time;

    /** @var int High-resolution time (nanoseconds) */
    private int $hrTime;

    /** @var DirInterface Path to the document root (html) */
    private DirInterface $dir;

    public function __construct(DirInterface $dir)
    {
        $this->time = time();
        $this->hrTime = hrtime(true);
        $this->handleDirectory($dir, '$dir');
        $this->dir = $dir;
    }

    public function time(): int
    {
        return $this->time;
    }

    public function hrTime(): int
    {
        return $this->hrTime;
    }

    public function dir(): DirInterface
    {
        return $this->dir;
    }

    /** @codeCoverageIgnore */
    private function handleDirectory(DirInterface $dir): void
    {
        if ($dir->exists() === false) {
            throw new DirNotExistsException;
        }
        if ($dir->path()->isWritable() === false) {
            throw new DirNotWritableException;
        }
    }
}
