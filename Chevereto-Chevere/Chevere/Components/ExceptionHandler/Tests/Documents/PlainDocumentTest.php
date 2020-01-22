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

namespace Chevere\Components\ExceptionHandler\Tests;

use Chevere\Components\ExceptionHandler\Documents\PlainDocument;
use Chevere\Components\ExceptionHandler\ExceptionHandler;
use PHPUnit\Framework\TestCase;
use LogicException;

final class PlainDocumentTest extends TestCase
{
    public function testConstruct(): void
    {
        $document =
            (new PlainDocument(
                new ExceptionHandler(new LogicException('Ups', 100))
            ))
            ->toString();

        echo $document . "\n";
    }
}