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

// @codeCoverageIgnoreStart

namespace Chevere\Components\Translation {
    use Chevere\Exceptions\Core\LogicException;
    use Gettext\Translator;
    use Gettext\TranslatorInterface;

function getTranslator(): TranslatorInterface
{
    try {
        return TranslatorInstance::get();
    } catch (LogicException $e) {
        return new Translator();
    }
}
}

namespace {
use function Chevere\Components\Translation\getTranslator;

    if (function_exists('_t') === false) {
        /**
         * Translates a string.
         */
        function _t(string $message)
        {
            return getTranslator()->gettext($message);
        }
    }
    if (function_exists('_tf') === false) {
        /**
         * Translates a formatted string with `sprintf`.
         */
        function _tf(string $message, ...$arguments)
        {
            return sprintf(_t($message), ...$arguments);
        }
    }
    if (function_exists('_tt') === false) {
        /**
         * Translates a formatted string with `strtr`.
         */
        function _tt(string $message, array $fromTo = [])
        {
            return strtr(_t($message), $fromTo);
        }
    }
    if (function_exists('_n') === false) {
        /**
         * Translates a formatted plural string.
         */
        function _n(string $singular, string $plural, int $count)
        {
            return getTranslator()->ngettext($singular, $plural, $count);
        }
    }
    if (function_exists('_nf') === false) {
        /**
         * Translates a formatted plural string with `sprintf`.
         */
        function _nf(string $singular, string $plural, int $count, ...$arguments)
        {
            return sprintf(
                _n($singular, $plural, $count),
                ...$arguments
            );
        }
    }
    if (function_exists('_nt') === false) {
        /**
         * Alias for `ngettext` with `strtr` handling.
         */
        function _nt(string $singular, string $plural, int $count, array $fromTo)
        {
            return strtr(_n($singular, $plural, $count), $fromTo);
        }
    }
}

// @codeCoverageIgnoreEnd
