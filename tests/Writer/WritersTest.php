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

namespace Chevere\Tests\Writer;

use function Chevere\Components\Writer\streamTemp;
use Chevere\Components\Writer\StreamWriter;
use Chevere\Components\Writer\Writers;
use Chevere\Interfaces\Writer\WriterInterface;
use PHPUnit\Framework\TestCase;

final class WritersTest extends TestCase
{
    public function testConstruct()
    {
        $writers = new Writers();
        foreach (['output', 'error', 'debug', 'log'] as $fnName) {
            $this->assertInstanceOf(
                WriterInterface::class,
                $writers->{$fnName}()
            );
        }
    }

    public function testWith(): void
    {
        $writer = new StreamWriter(streamTemp(''));
        $writers = (new Writers())->with($writer);
        foreach (['output', 'error', 'debug', 'log'] as $name) {
            $this->assertSame($writer, $writers->{$name}());
        }
    }

    public function testWithX(): void
    {
        foreach (['output', 'error', 'debug', 'log'] as $name) {
            $writer = new StreamWriter(streamTemp(''));
            $withFn = 'with' . ucfirst($name);
            $writers = (new Writers())->{$withFn}($writer);
            $this->assertSame($writer, $writers->{$name}());
        }
    }
}
