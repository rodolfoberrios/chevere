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

namespace Chevere\Traits;

use LogicException;
use Chevere\File;
use Chevere\Path\Path;
use Chevere\Message;

trait CallableTrait
{
    /**
     * Retuns the callable some (callable string, callable relative filepath).
     *
     * @param string $callableString a callable string
     */
    public function getCallableSome(string $callableString): ?string
    {
        if (is_callable($callableString)) {
            return $callableString;
        } else {
            if (class_exists($callableString)) {
                if (method_exists($callableString, '__invoke')) {
                    return (string) $callableString;
                } else {
                    throw new LogicException(
                        (new Message('Missing %s method in class %c'))
                            ->code('%s', '__invoke')
                            ->code('%c', $callableString)
                            ->toString()
                    );
                }
            } else {
                $callableFile = Path::fromIdentifier($callableString);
                $this->checkCallableFile($callableFile);

                return Path::relative($callableFile);
            }
        }
    }

    /**
     * Checks if a callable file exists.
     */
    protected function checkCallableFile(string $callableFile)
    {
        // Check callable existance
        if (!File::exists($callableFile, true)) {
            throw new LogicException(
                (new Message("Callable %s doesn't exists."))
                    ->code('%s', $callableFile)
                    ->toString()
            );
        }
        // Had to make this sandwich since we are calling an anon callable.
        $errorLevel = error_reporting();
        error_reporting($errorLevel ^ E_NOTICE);
        $anonCallable = include $callableFile;
        error_reporting($errorLevel);
        // Check callable
        if (!is_callable($anonCallable)) {
            throw new LogicException(
                (new Message('File %f is not a valid %t.'))
                    ->code('%f', $callableFile)
                    ->code('%t', 'callable')
                    ->toString()
            );
        }
    }
}