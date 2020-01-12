<?php

/*
 * This file is part of Chevere.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Chevere\Components\VarDump\Outputters;

use Chevere\Components\VarDump\Contracts\OutputterContract;

final class HtmlOutputter extends AbstractOutputter
{
    /**
     * {@inheritdoc}
     */
    public function prepare(): OutputterContract
    {
        if (false === headers_sent()) {
            $this->output .= '<html style="background: ' . $this->dumper::BACKGROUND_SHADE . ';"><head></head><body>';
        }
        $this->output .= '<pre style="' . $this->dumper::STYLE . '">';

        return $this;
    }

    public function printOutput(): void
    {
        echo $this->output;
    }
}
