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

use Chevere\Components\Api\EndpointMethod;
use Chevere\Components\Api\Tests\_resources\controllers\GetArticleController;
use Chevere\Components\Controller\Interfaces\ControllerInterface;

return new class() extends EndpointMethod {
    public function controller(): ControllerInterface
    {
        return new GetArticleController;
    }
};
