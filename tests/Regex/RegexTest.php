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

namespace Chevere\Tests\Regex;

use Chevere\Components\Regex\Regex;
use Chevere\Exceptions\Core\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class RegexTest extends TestCase
{
    public function testConstructInvalidArgument(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Regex('#');
    }

    public function testConstruct(): void
    {
        $pattern = '\w+';
        $patternAnchors = "^${pattern}$";
        $patternDelimitersAnchors = "/${patternAnchors}/";
        $regex = new Regex($patternDelimitersAnchors);
        $this->assertSame($patternDelimitersAnchors, $regex->toString());
        $this->assertSame($patternAnchors, $regex->toNoDelimiters());
        $this->assertSame($pattern, $regex->toNoDelimitersNoAnchors());
    }

    public function testMatch(): void
    {
        $test = 'Hello World!';
        $pattern = '/^' . $test . '$/';
        $regex = new Regex($pattern);
        $this->assertSame([$test], $regex->match($test));
        $this->assertSame([[$test]], $regex->matchAll($test));
    }

    public function testMatchRegex(): void
    {
        $pattern = '/^id-[\d]+$/';
        $test = 'id-123';
        $regex = new Regex($pattern);
        $this->assertSame([$test], $regex->match($test));
        $this->assertSame([[$test]], $regex->matchAll($test));
    }
}
