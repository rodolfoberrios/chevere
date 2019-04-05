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

namespace Chevereto\Core;

abstract class Container
{
    /** @var array An array containing propName => className|null */
    protected $objects = [];

    /**
     * Retrieves the $objects property.
     */
    public function getObjectSignatures(): array
    {
        return $this->objects;
    }

    /**
     * Provides ::hasAlgo, ::getAlgoObject magic. "Algo" refers to an object property ($algo) known by the Container.
     */
    final public function __call(string $name, array $arguments = null)
    {
        if (Utils\Str::startsWith('has', $name)) {
            $propertyName = lcfirst(substr($name, 3));

            return $this->_callHasAlgo($propertyName);
        }
        // if (Utils\Str::startsWith('get', $name) && Utils\Str::endsWith('Object', $name)) {
        //     // Chars: get=3, Object=6
        //     return $this->_callGetAlgoObject(substr(substr($name, 0, -6), 3));
        // }
    }

    /**
     * The ::hasAlgo magic.
     */
    private function _callHasAlgo(string $propertyName): bool
    {
        $property = $this->{$propertyName};
        if (!isset($property)) {
            return false;
        }
        $acceptedClass = $this->objects[$propertyName] ?? null;
        if (isset($acceptedClass)) {
            return $property instanceof $acceptedClass;
        }

        return true;
    }

    /*
     * The ::GetAlgoObject is similar to a getAlgo getter, but this one won't throw type exception.
     */
    // private function _callGetAlgoObject(string $algo): ?object
    // {
    //     $propertyName = lcfirst($algo);
    //     $getMethod = 'get'.$algo;
    //     dd($algo, $propertyName, $getMethod);

    //     return null;
    // }
}
