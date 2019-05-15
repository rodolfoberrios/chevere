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

class AppStatic
{
    public static function getBuildFilePath(): string
    {
        return ROOT_PATH.App\PATH.'build';
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
     *
     * @return HttpRequest|null
     */
    public static function requestInstance(): ?HttpRequest
    {
        // Request isn't there when doing cli (unless you run the request command)
        return isset(static::$instance) && isset(static::$instance->httpRequest)
            ? static::$instance->httpRequest
            : null;
    }

    /**
     * Provides access to the App Runtime instance.
     *
     * @return Runtime|null
     */
    public static function runtimeInstance(): ?Runtime
    {
        if (isset(static::$instance)) {
            if (isset(static::$instance->runtime)) {
                return static::$instance->getRuntime();
            }
        }

        return null;
    }
}