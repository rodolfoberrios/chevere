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

namespace Chevere\Components\Parameter;

use Chevere\Components\Message\Message;
use Chevere\Exceptions\Core\ArgumentCountException;
use Chevere\Exceptions\Core\InvalidArgumentException;
use Chevere\Exceptions\Core\OutOfBoundsException;
use Chevere\Interfaces\Parameter\ArgumentsInterface;
use Chevere\Interfaces\Parameter\ParameterInterface;
use Chevere\Interfaces\Parameter\ParametersInterface;
use Chevere\Interfaces\Parameter\StringParameterInterface;
use Throwable;

final class Arguments implements ArgumentsInterface
{
    public array $arguments;

    private ParametersInterface $parameters;

    private array $errors;

    public function __construct(ParametersInterface $parameters, mixed ...$namedArguments)
    {
        $this->parameters = $parameters;
        /** @var array<string, mixed> $namedArguments */
        $this->arguments = $namedArguments;
        $this->assertCount();
        $this->errors = [];
        foreach ($this->parameters->getGenerator() as $name => $parameter) {
            $this->handleParameter($name, $parameter);
        }
        if (count($this->errors) !== 0) {
            throw new InvalidArgumentException(
                (new Message(implode(', ', $this->errors)))
            );
        }
    }

    public function parameters(): ParametersInterface
    {
        return $this->parameters;
    }

    public function toArray(): array
    {
        return $this->arguments;
    }

    public function withArgument(string $name, $value): ArgumentsInterface
    {
        $this->assertHasParameter($name);
        $this->assertType($name, $value);
        $new = clone $this;
        $new->arguments[$name] = $value;

        return $new;
    }

    public function has(string $name): bool
    {
        return isset($this->arguments[$name]);
    }

    public function get(string $name)
    {
        try {
            return $this->arguments[$name];
        } catch (Throwable $e) {
            throw new OutOfBoundsException(
                (new Message('Argument %name% not found'))
                    ->code('%name%', $name),
            );
        }
    }

    public function getBoolean(string $name): bool
    {
        return $this->get($name);
    }

    public function getString(string $name): string
    {
        return $this->get($name);
    }

    public function getInteger(string $name): int
    {
        return $this->get($name);
    }

    public function getFloat(string $name): float
    {
        return $this->get($name);
    }

    public function getArray(string $name): array
    {
        return $this->get($name);
    }

    private function assertCount(): void
    {
        $argumentsKeys = array_keys($this->arguments);
        $diffRequired = array_diff(
            $this->parameters->required()->toArray(),
            $argumentsKeys
        );
        if ($diffRequired !== []) {
            throw new ArgumentCountException(
                (new Message('Missing required argument(s): %missing%'))
                    ->code('%missing%', implode(', ', $diffRequired))
            );
        }
        $diffExtra = array_diff(
            $argumentsKeys,
            $this->parameters->keys()
        );
        if ($diffExtra !== []) {
            throw new ArgumentCountException(
                (new Message('Passing extra arguments: %extra%'))
                    ->code('%extra%', implode(', ', $diffExtra))
            );
        }
    }

    private function assertType(string $name, $value): void
    {
        $parameter = $this->parameters->get($name);
        $type = $parameter->type();
        if (! $type->validate($value)) {
            throw new InvalidArgumentException(
                (new Message('Parameter %name%: Expecting value of type %expected%, %provided% provided'))
                    ->strong('%name%', $name)
                    ->strong('%expected%', $type->typeHinting())
                    ->code('%provided%', get_debug_type($value))
            );
        }
        if ($parameter instanceof StringParameterInterface) {
            /** @var StringParameterInterface $parameter */
            $this->assertStringArgument($name, $parameter, $value);
        }
    }

    /**
     * @throws OutOfBoundsException
     */
    private function assertHasParameter(string $name): void
    {
        if ($this->parameters->has($name) === false) {
            throw new OutOfBoundsException(
                (new Message('Parameter %parameter% not found'))
                    ->code('%parameter%', $name)
            );
        }
    }

    /**
     * @throws InvalidArgumentException
     */
    private function assertStringArgument(string $name, StringParameterInterface $parameter, string $argument): void
    {
        $regexString = $parameter->regex()->toString();
        if (preg_match($regexString, $argument) !== 1) {
            throw new InvalidArgumentException(
                (new Message("Parameter %name%: Argument value provided doesn't match the regex %regex%"))
                    ->strong('%name%', $name)
                    ->code('%parameter%', $name)
                    ->code('%regex%', $regexString)
            );
        }
    }

    private function handleParameter(string $name, ParameterInterface $parameter): void
    {
        $this->handleParameterDefault($name, $parameter);
        if ($this->parameters->isOptional($name)) {
            return;
        }

        try {
            $this->assertType($name, $this->get($name));
        } catch (Throwable $e) {
            $this->errors[] = $e->getMessage();
        }
    }

    private function handleParameterDefault(string $name, ParameterInterface $parameter): void
    {
        if (! $this->has($name)
            && $parameter instanceof StringParameterInterface
            && $parameter->default() !== ''
        ) {
            $this->arguments[$name] = $parameter->default();
        }
    }
}
