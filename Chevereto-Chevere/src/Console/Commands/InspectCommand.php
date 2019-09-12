<?php

declare(strict_types=1);

/*
 * This file is part of Chevere.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

// TODO: Static inspection!
// TODO: Deprecate callables by file
// TODO: Non-ambiguous types. Use $callableString $callableObject?

namespace Chevere\Console\Commands;

use Reflector;
use ReflectionMethod;
use ReflectionFunction;
use Chevere\Contracts\App\LoaderContract;
use Chevere\Contracts\Controller\ControllerContract;
use Chevere\Console\Command;
use Chevere\Utility\Str;

/**
 * The InspectCommand allows to get callable information using CLI.
 *
 * Usage:
 * php app/console inspect "callable"
 */
final class InspectCommand extends Command
{
    const NAME = 'inspect';
    const DESCRIPTION = 'Inspect any callable';
    const HELP = 'This command allows you to inspect any callable';

    const ARGUMENTS = [
        ['callable', Command::ARGUMENT_REQUIRED, 'A fully-qualified callable name'],
    ];

    /** @var array */
    protected $arguments = [];

    /** @var Reflector */
    protected $reflector;

    /** @var object|string */
    protected $callable;

    /** @var string */
    protected $method;

    /** @var string */
    protected $callableInput;

    public function callback(LoaderContract $loader): int
    {
        $this->callableInput = (string) $this->cli()->input()->getArgument('callable');
        if (is_subclass_of($this->callableInput, ControllerContract::class)) {
            $this->callable = $this->callableInput;
            $this->method = '__invoke';
        } else {
            if (is_callable($this->callableInput)) {
                $this->callable = $this->callableInput;
            }
        }

        $this->handleSetMethod();
        $this->handleSetReflector();
        $this->cli()->style()->block($this->callableInput, 'INSPECTED', 'OK', ' ', true);
        $this->processParametersArguments();
        $this->handleProcessArguments();

        return 1;
    }

    private function handleSetMethod(): void
    {
        if (is_object($this->callable)) {
            $this->method = '__invoke';
        } else {
            if (Str::contains('::', $this->callable)) {
                $callableExplode = explode('::', $this->callable);
                $this->callable = $callableExplode[0];
                $this->method = $callableExplode[1];
            }
        }
    }

    private function handleSetReflector(): void
    {
        if (isset($this->method)) {
            $this->setReflectionMethod();
        } else {
            $this->setReflectionFunction();
        }
    }

    private function setReflectionMethod()
    {
        $this->reflector = new ReflectionMethod($this->callable, $this->method);
    }

    private function setReflectionFunction()
    {
        $this->reflector = new ReflectionFunction($this->callable);
    }

    private function processParametersArguments(): void
    {
        $i = 0;
        foreach ($this->reflector->getParameters() as $parameter) {
            $aux = '';
            if ($parameter->getType()) {
                $aux .= $parameter->getType() . ' ';
            }
            $aux .= '$' . $parameter->getName();
            if ($parameter->isDefaultValueAvailable()) {
                $aux .= ' = ' . ($parameter->getDefaultValue() ?? 'null');
            }
            // $res = $resource[$parameter->getName()] ?? null;
            // if (isset($res)) {
            //     $aux .= ' '.VarDump::wrap(VarDump::_OPERATOR, '--description '.$res['description'].' --regex '.$res['regex']);
            // }
            $this->arguments[] = "#$i $aux";
            ++$i;
        }
    }

    private function handleProcessArguments(): void
    {
        if (null != $this->arguments) {
            $this->processArguments();
        } else {
            $this->processNoArguments();
        }
    }

    private function processArguments(): void
    {
        $this->cli()->style()->text(['<fg=yellow>Arguments:</>']);
        $this->cli()->style()->listing($this->arguments);
    }

    private function processNoArguments(): void
    {
        $this->cli()->style()->text(['<fg=yellow>No arguments</>', null]);
    }
}