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

namespace Chevereto\Chevere;

use RuntimeException;

abstract class AppStatic
{
    /** @var App */
    protected static $instance;

    /** @var Runtime */
    protected static $defaultRuntime;

    protected static function setStaticInstance(App $app)
    {
        static::$instance = $app;
    }

    public static function setDefaultRuntime(Runtime $runtime): void
    {
        static::$defaultRuntime = $runtime;
    }

    public static function getDefaultRuntime(): Runtime
    {
        return static::$defaultRuntime;
    }

    /**
     * Provides access to the App HttpRequest instance.
     */
    public static function requestInstance(): HttpRequest
    {
        // Request isn't there when doing cli (unless you run the request command)
        if (isset(static::$instance) && isset(static::$instance->httpRequest)) {
            return static::$instance->httpRequest;
        }

        throw new RuntimeException('NO REQUEST INSTANCE EVERYTHING SMELLS!');
    }

    /**
     * Provides access to the App Runtime instance.
     */
    public static function runtimeInstance(): Runtime
    {
        if (isset(static::$instance) && $runtimeInstance = static::$instance->runtime) {
            return $runtimeInstance;
        }
        throw new RuntimeException('NO RUNTIME INSTANCE EVERYTHING BURNS!');
    }
}
