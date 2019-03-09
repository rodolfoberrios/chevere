<?php declare(strict_types=1);
/*
 * This file is part of Chevereto\Core.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Chevereto\Core;

use Exception;

/**
 * Router works by adding registers (route collections) which contain Route
 * declarations which later on are used to make the final routing table.
 *
 * The resulting routing table can be cached. See @cache().
 */
class Router
{
    // Path regex keys
    const PATH_COMPONENT = 'path_component';
    const PATH_WILDCARD = 'wildcard';
    const PATH_WILDCARD_CAPTURE = 'wildcard_capture';
    // Path regex array
    const PATH_REGEX = [
        self::PATH_COMPONENT => '[A-z0-9\.\-\%]+', // component
        self::PATH_WILDCARD => '{[A-z0-9]+}', // {wildcard}
        self::PATH_WILDCARD_CAPTURE => '{([A-z0-9]+)}', // {wildcard}
    ];

    /**
     * Contains the registry of files that generates routing.
     */
    protected $registers = [];
    /**
     * Contains the Routes $routing table & found $route
     */
    public function __construct()
    {
    }
    /**
     * Add a route collection to the registry.
     *
     * @param string $fileHandle File handle to look for.
     * @param string $context Context for the file handle.
     */
    public function register(string $fileHandle, string $context = null) : self
    {
        $filePath = Path::fromHandle(...func_get_args());
        $relativeFilePath = Path::relative($filePath);
        if (in_array($filePath, $this->registers)) {
            throw new Exception(
                (new Message('Register %s has been already added to the route registry.'))
                ->code('%s', $relativeFilePath)
            );
        }
        $this->registers[] = $filePath;
        return $this;
    }
    /**
     * Makes the $routing table
     */
    public function make()
    {
        if ($this->registers == null) {
            throw new Exception(
                (new Message('Unable to execute %s - No route registers found.'))
                    ->code('%s', __METHOD__)
            );
        }
        foreach ($this->registers as $k => $register) {
            include $register;
        }
        Routes::instance()->process();
    }
    public function getRegisters() : array
    {
        return $this->registers;
    }
}
class RouterException extends CoreException
{
}