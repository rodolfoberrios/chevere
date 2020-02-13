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

namespace Chevere\Components\VarDump\Processors;

use Chevere\Components\Type\Interfaces\TypeInterface;

final class StringProcessor extends AbstractProcessor
{
    public function type(): string
    {
        return TypeInterface::STRING;
    }

    protected function process(): void
    {
        $this->info = 'length=' . mb_strlen($this->varProcess->dumpeable()->var());
        $this->varProcess->writer()->write(
            implode(' ', [
                $this->typeHighlighted(),
                $this->varProcess->formatter()->filterEncodedChars(
                    $this->varProcess->dumpeable()->var()
                ),
                $this->highlightParentheses($this->info)
            ])
        );
    }
}
