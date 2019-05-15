<?php

declare(strict_types=1);

/*
 * This file is part of Chevereto\Core.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chevereto\Chevere;

use LogicException;
use ReflectionMethod;
use ReflectionFunction;
use ReflectionParameter;

/**
 * CallableWrap provides a object oriented way to interact with Chevereto\Core accepted callable strings.
 *
 * Accepted callable strings are:
 *
 * - A callable (function, method name)
 * - A class implementing ::__invoke
 * - A fileHandle string representing the path of a file wich returns a callable
 */
class CallableWrap extends CallableWrapProcessor
{
    public function __construct(string $callableHandle)
    {
        $this
            ->setIsFileHandle(false)
            ->setCallableHandle($callableHandle);
        // Direct processing for callable strings and invocable classes
        if (is_callable($callableHandle)) {
            $this
                ->setCallable($callableHandle)
                ->setIsAnon(false)
                ->prepare();
        } else {
            if (class_exists($callableHandle)) {
                if (method_exists($callableHandle, '__invoke')) {
                    $this
                        ->setCallable(/* @scrutinizer ignore-type */ new $callableHandle()) // string!
                        ->setClass(/* @scrutinizer ignore-type */ $callableHandle) // string!
                        ->setMethod('__invoke')
                        ->setIsAnon(false)
                        ->prepare();
                } else {
                    throw new LogicException(
                        (string)
                            (new Message('Missing magic method %s in class %c.'))
                                ->code('%s', '__invoke')
                                ->code('%c', $callableHandle)
                    );
                }
            }
        }
        // Some work needed when dealing with fileHandle
        if (!isset($this->callable)) {
            $this->processCallableHandle();
        }
    }

    protected function setCallableHandle(string $callableHandle): self
    {
        $this->callableHandle = $callableHandle;

        return $this;
    }

    public function getCallableHandle(): string
    {
        return $this->callableHandle;
    }

    protected function setCallable(callable $callable): self
    {
        $this->callable = $callable;

        return $this;
    }

    public function getCallable(): callable
    {
        return $this->callable;
    }

    protected function setCallableFilepath(string $filepath): self
    {
        $this->callableFilepath = $filepath;

        return $this;
    }

    public function getCallableFilepath(): ?string
    {
        return $this->callableFilepath ?? null;
    }

    protected function setType(string $type): self
    {
        if (!in_array($type, static::TYPES)) {
            throw new LogicException(
                (string)
                    (new Message('Invalid type %s, expecting one of these: %v.'))
                        ->code('%s', $type)
                        ->code('%v', implode(', ', static::TYPES))
            );
        }
        $this->type = $type;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    protected function setClass(string $class): self
    {
        $this->class = $class;

        return $this;
    }

    public function getClass(): ?string
    {
        return $this->class ?? null;
    }

    protected function setMethod(string $method): self
    {
        $this->method = $method;

        return $this;
    }

    public function getMethod(): ?string
    {
        return $this->method ?? null;
    }

    protected function setIsFileHandle(bool $isFileHandle): self
    {
        $this->isFileHandle = $isFileHandle;

        return $this;
    }

    public function isFileHandle(): bool
    {
        return $this->isFileHandle;
    }

    protected function setIsAnon(bool $isAnon): self
    {
        $this->isAnon = $isAnon;

        return $this;
    }

    public function isAnon(): bool
    {
        return $this->isAnon;
    }

    // Process the callable and fill the object properties
    protected function prepare()
    {
        if (isset($this->class)) {
            $this->setType(isset($this->method) ? static::TYPE_CLASS : static::TYPE_FUNCTION);
        } else {
            if (is_object($this->getCallable())) {
                $this->setMethod('__invoke');
                $reflection = new \ReflectionClass($this->getCallable());
                $this->setType(static::TYPE_CLASS);
                $this->setIsAnon($reflection->isAnonymous());
                $this->setClass($this->isAnon() ? 'class@anonymous' : $reflection->getName());
            } else {
                $this->setType(static::TYPE_FUNCTION);
                if (isset($this->callableHandleMethodExplode)) {
                    $this
                        ->setClass($this->callableHandleMethodExplode[0])
                        ->setMethod($this->callableHandleMethodExplode[1]);
                }
            }
        }
    }

    protected function setParameters(array $parameters): self
    {
        $this->parameters = $parameters;

        return $this;
    }

    /**
     * Undocumented function.
     *
     * @return array an array containing ReflectionParameter members
     */
    public function getParameters(): ?array
    {
        if (!isset($this->parameters)) {
            $this->processParameters();
        }

        return $this->parameters ?? null;
    }

    protected function getReflectionFunction(): ?ReflectionFunction
    {
        return $this->reflectionFunction ?? null;
    }

    protected function getReflectionMethod(): ?ReflectionMethod
    {
        return $this->reflectionMethod ?? null;
    }

    protected function hasReflection(): bool
    {
        return isset($this->reflectionFunction) || isset($this->reflectionMethod);
    }

    public function getReflection(): \ReflectionFunctionAbstract
    {
        return isset($this->reflectionFunction)
            ? $this->getReflectionFunction()
            : $this->getReflectionMethod();
    }

    /**
     * Pass arguments to the callable which will be typehinted by this class.
     *
     * @param array $passedArguments
     *
     * @return self
     */
    public function setPassedArguments(array $passedArguments): self
    {
        $this->passedArguments = $passedArguments;

        return $this;
    }

    public function getPassedArguments(): ?array
    {
        return $this->passedArguments ?? null;
    }

    /**
     * Set the callable arguments.
     *
     * @param array $arguments callable arguments
     *
     * @return self
     */
    protected function setArguments(array $arguments): self
    {
        $this->arguments = $arguments;

        return $this;
    }

    public function getArguments(): array
    {
        if (!isset($this->arguments)) {
            $this->processArguments();
        }

        return $this->arguments ?? null;
    }
}