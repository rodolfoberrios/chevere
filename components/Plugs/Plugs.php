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

namespace Chevere\Components\Plugs;

use Chevere\Components\ClassMap\ClassMap;
use Chevere\Components\Filesystem\FileFromString;
use Chevere\Components\Filesystem\FilePhp;
use Chevere\Components\Filesystem\FilePhpReturn;
use Chevere\Components\Hooks\Exceptions\HooksClassNotRegisteredException;
use Chevere\Components\Hooks\Exceptions\HooksFileNotFoundException;
use Chevere\Components\Message\Message;
use Chevere\Components\Plugs\PlugsQueue;
use LogicException;
use RuntimeException;
use Throwable;
use TypeError;

final class Plugs
{
    private ClassMap $hookablesToHooks;

    private PlugsQueue $_plugsQueue;

    public function __construct(ClassMap $classMap)
    {
        $this->hookablesToHooks = $classMap;
    }

    public function has(string $hookable): bool
    {
        return $this->hookablesToHooks->has($hookable);
    }

    /**
     * @throws HooksClassNotRegisteredException
     * @throws HooksFileNotFoundException
     * @throws RuntimeException if unable to load the hooks file
     * @throws LogicException if the contents of the hooks file are invalid
     */
    public function getQueue(string $className): PlugsQueue
    {
        if (!$this->has($className)) {
            throw new HooksClassNotRegisteredException(
                (new Message("Class %className% doesn't exists in the class map"))
                    ->code('%className%', $className)
            );
        }
        $hooksPath = $this->hookablesToHooks->get($className);
        if (stream_resolve_include_path($hooksPath) === false) {
            throw new HooksFileNotFoundException(
                (new Message("File %fileName% doesn't exists"))
                    ->code('%fileName%', $hooksPath)
            );
        }
        // @codeCoverageIgnoreStart
        try {
            $fileReturn = new FilePhpReturn(new FilePhp(new FileFromString($hooksPath)));
            $fileReturn = $fileReturn->withStrict(false);
            try {
                $this->_plugsQueue = $fileReturn->var();
            } catch (TypeError $e) {
                throw new LogicException(
                    (new Message('Return of %filePath% is not of type %type%'))
                        ->code('%filePath%', $hooksPath)
                        ->code('%type%', PlugsQueue::class)
                        ->toString()
                );
            }
        } catch (Throwable $e) {
            throw new RuntimeException($e->getMessage());
        }
        // @codeCoverageIgnoreEnd

        return $this->_plugsQueue;
    }
}