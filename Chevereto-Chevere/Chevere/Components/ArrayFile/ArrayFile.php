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

namespace Chevere\Components\ArrayFile;

use Chevere\Components\ArrayFile\Exceptions\ArrayFileTypeException;
use Chevere\Components\File\Exceptions\FileReturnInvalidTypeException;
use Chevere\Components\File\FileReturn;
use Chevere\Components\Message\Message;
use Chevere\Contracts\ArrayFile\ArrayFileContract;
use Chevere\Contracts\File\FileContract;
use Chevere\Contracts\File\FilePhpContract;
use Chevere\Contracts\Type\TypeContract;

/**
 * ArrayFile provides a object oriented method to interact with array files (return []).
 */
final class ArrayFile implements ArrayFileContract
{
    /** @var array The array returned by the file */
    private $array;

    /** @var FilePhpContract */
    private $filePhp;

    /** @var TypeContract */
    private $type;

    /**
     * {@inheritdoc}
     */
    public function __construct(FilePhpContract $filePhp)
    {
        $this->filePhp = $filePhp;
        $this->filePhp->file()->assertExists();
        $fileReturn = (new FileReturn($this->filePhp))
            ->withNoStrict();
        $this->array = $fileReturn->return();
        $this->validateReturnIsArray();
    }

    /**
     * {@inheritdoc}
     */
    public function withMembersType(TypeContract $type): ArrayFileContract
    {
        $new = clone $this;
        $new->type = $type;
        $new->validateMembers();

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function file(): FileContract
    {
        return $this->filePhp->file();
    }

    /**
     * {@inheritdoc}
     */
    public function array(): array
    {
        return $this->array;
    }

    private function validateReturnIsArray(): void
    {
        $type = gettype($this->array);
        if ('array' !== $type) {
            throw new FileReturnInvalidTypeException(
                (new Message('Expecting file %path% return type array, %returnType% provided'))
                    ->code('%path%', $this->filePhp->file()->path()->absolute())
                    ->code('%returnType%', $type)
                    ->toString()
            );
        }
    }

    private function validateMembers(): void
    {
        $validator = $this->type->validator();
        foreach ($this->array as $k => $val) {
            $validate = $validator($val);
            if ($validate) {
                $validate = $this->type->validate($val);
            }
            if (!$validate) {
                $this->handleInvalidation($k, $val);
            }
        }
    }

    private function handleInvalidation($k, $val): void
    {
        $type = gettype($val);
        if ('object' == $type) {
            $type .= ' ' . get_class($val);
        }
        throw new ArrayFileTypeException(
            (new Message('Expecting array containing only %membersType% members, type %type% found at %filepath% (key %key%)'))
                ->code('%membersType%', $this->type->typeHinting())
                ->code('%filepath%', $this->filePhp->file()->path()->absolute())
                ->code('%type%', $type)
                ->code('%key%', $k)
                ->toString()
        );
    }
}
