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

namespace Chevere\Components\ClassMap;

use Chevere\Components\DataStructure\Traits\MapToArrayTrait;
use Chevere\Components\DataStructure\Traits\MapTrait;
use Chevere\Components\Message\Message;
use Chevere\Exceptions\Core\ClassNotExistsException;
use Chevere\Exceptions\Core\OutOfBoundsException;
use Chevere\Exceptions\Core\OverflowException;
use Chevere\Interfaces\ClassMap\ClassMapInterface;
use Ds\Map;

final class ClassMap implements ClassMapInterface
{
    use MapTrait;
    use MapToArrayTrait;

    /**
     * @var Map [className => key]
     */
    private Map $map;

    /**
     * @var Map [key => className]
     */
    private Map $flip;

    public function __construct()
    {
        $this->map = new Map();
        $this->flip = new Map();
    }

    public function __clone()
    {
        $this->map = new Map($this->map->toArray());
        $this->flip = new Map($this->flip->toArray());
    }

    public function withPut(string $className, string $key): ClassMapInterface
    {
        if (! class_exists($className) && ! interface_exists($className)) {
            throw new ClassNotExistsException(
                (new Message("Class name or interface %className% doesn't exists"))
                    ->strong('%className%', $className)
            );
        }
        $known = $this->flip[$key] ?? null;
        if ($known && $known !== $className) {
            throw new OverflowException(
                (new Message('Attempting to map %className% to the same mapping of %known% -> %string%'))
                    ->code('%className%', $className)
                    ->code('%known%', $known)
                    ->code('%string%', $key)
            );
        }
        $new = clone $this;
        $new->map[$className] = $key;
        $new->flip[$key] = $className;

        return $new;
    }

    public function has(string $className): bool
    {
        return $this->map->hasKey($className);
    }

    public function hasKey(string $key): bool
    {
        return $this->flip->hasKey($key);
    }

    public function key(string $className): string
    {
        if (! $this->has($className)) {
            throw new OutOfBoundsException(
                (new Message("Class %className% doesn't exists in the class map"))
                    ->code('%className%', $className)
            );
        }

        return $this->map[$className];
    }

    public function keys(): array
    {
        return $this->flip->keys()->toArray();
    }

    public function className(string $key): string
    {
        if (! $this->hasKey($key)) {
            throw new OutOfBoundsException(
                (new Message("Key %key% doesn't map any class"))
                    ->code('%key%', $key)
            );
        }

        return $this->flip[$key];
    }
}
