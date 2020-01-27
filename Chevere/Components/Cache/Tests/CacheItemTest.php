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

namespace Chevere\Components\Cache\Tests;

use Chevere\Components\Cache\CacheItem;
use Chevere\Components\File\File;
use Chevere\Components\File\FilePhp;
use Chevere\Components\File\FileReturn;
use Chevere\Components\Path\Path;
use Chevere\Components\Cache\Interfaces\CacheItemInterface;
use Chevere\Components\Path\Interfaces\PathInterface;
use PHPUnit\Framework\TestCase;

final class CacheItemTest extends TestCase
{
    private function getCacheItem(PathInterface $path): CacheItemInterface
    {
        return
            new CacheItem(
                new FileReturn(
                    new FilePhp(
                        new File($path)
                    )
                )
            );
    }

    // private function writeSerialized(PathInterface $path): void
    // {
    //     $fileReturn =
    //         new FileReturn(
    //             new FilePhp(
    //                 new File($path)
    //             )
    //         );
    //     $fileReturn->put(
    //         new VariableExport($path)
    //     );
    // }

    public function testNotSerialized(): void
    {
        $path = new Path(__DIR__ . '/resources/return.php');
        $cacheItem = $this->getCacheItem($path);
        $var = include $path->absolute();
        $this->assertSame($var, $cacheItem->raw());
        $this->assertSame($var, $cacheItem->var());
    }

    public function testSerialized(): void
    {
        $path = new Path(__DIR__ . '/resources/return-serialized.php');
        $cacheItem = $this->getCacheItem($path);
        $var = include $path->absolute();
        $this->assertSame($var, $cacheItem->raw());
        $this->assertEquals(unserialize($var), $cacheItem->var());
    }
}