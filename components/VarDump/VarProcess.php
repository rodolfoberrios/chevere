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

namespace Chevere\Components\VarDump;

use Chevere\Components\Type\Interfaces\TypeInterface;
use Chevere\Components\VarDump\Interfaces\VarDumpeableInterface;
use Chevere\Components\VarDump\Interfaces\FormatterInterface;
use Chevere\Components\VarDump\Interfaces\VarProcessInterface;
use Chevere\Components\Writers\Interfaces\WriterInterface;

/**
 * The Chevere VarProcess.
 */
final class VarProcess implements VarProcessInterface
{
    private WriterInterface $writer;

    private VarDumpeableInterface $dumpeable;

    private FormatterInterface $formatter;

    private int $indent = 0;

    private string $indentString = '';

    private int $depth = -1;

    public array $known = [];

    public function __construct(
        WriterInterface $writer,
        VarDumpeableInterface $dumpeable,
        FormatterInterface $formatter
    ) {
        $this->writer = $writer;
        $this->dumpeable = $dumpeable;
        $this->formatter = $formatter;
        ++$this->depth;
    }

    public function writer(): WriterInterface
    {
        return $this->writer;
    }

    public function dumpeable(): VarDumpeableInterface
    {
        return $this->dumpeable;
    }

    public function formatter(): FormatterInterface
    {
        return $this->formatter;
    }

    public function withIndent(int $indent): VarProcessInterface
    {
        $new = clone $this;
        $new->indent = $indent;
        $new->indentString = $new->formatter
            ->indent($indent);

        return $new;
    }

    public function indent(): int
    {
        return $this->indent;
    }

    public function withDepth(int $depth): VarProcessInterface
    {
        $new = clone $this;
        $new->depth = $depth;

        return $new;
    }

    public function depth(): int
    {
        return $this->depth;
    }

    public function withKnownObjects(array $known): VarProcessInterface
    {
        $new = clone $this;
        $new->known = $known;

        return $new;
    }

    public function known(): array
    {
        return $this->known;
    }

    public function withProcessor(): VarProcessInterface
    {
        $new = clone $this;
        $processorName = $new->dumpeable->processorName();
        if (in_array($new->dumpeable->type(), [TypeInterface::ARRAY, TypeInterface::OBJECT])) {
            ++$new->indent;
        }
        new $processorName($new);

        return $new;
    }

    public function indentString(): string
    {
        return $this->indentString;
    }
}
