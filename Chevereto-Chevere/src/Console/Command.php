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

namespace Chevere\Console;

use LogicException;
use Symfony\Component\Console\Command\Command as ConsoleCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Chevere\Chevere;
use Chevere\Interfaces\CommandInterface;

class Command extends ConsoleCommand implements CommandInterface
{
    const ARGUMENT_REQUIRED = InputArgument::REQUIRED;
    const ARGUMENT_OPTIONAL = InputArgument::OPTIONAL;
    const ARGUMENT_IS_ARRAY = InputArgument::IS_ARRAY;

    const OPTION_NONE = InputOption::VALUE_NONE;
    const OPTION_REQUIRED = InputOption::VALUE_REQUIRED;
    const OPTION_OPTIONAL = InputOption::VALUE_OPTIONAL;
    const OPTION_IS_ARRAY = InputOption::VALUE_IS_ARRAY;

    /** @var Cli */
    protected $cli;

    final public function __construct(Cli $cli)
    {
        $this->cli = $cli;
        parent::__construct();
    }

    /**
     * Callback contains the actual command in-app instructions.
     */
    public function callback(Chevere $chevere)
    {
        throw new LogicException('You must override the '.__FUNCTION__.'() method in the concrete command class.');
    }

    /**
     * Sets the Cli command to execute. Used internally by Symfony.
     */
    final protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->cli->command = $this;
    }
}
