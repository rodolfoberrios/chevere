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

namespace Chevere\Runtime;

use Chevere\Data\Data;
use Chevere\Contracts\Runtime\RuntimeSetContract;
use Chevere\Contracts\DataContract;
use Chevere\Data\Traits\DataKeyTrait;
use ReflectionClass;

/**
 * Runtime applies runtime config and provide data about the App Runtime.
 */
final class Runtime
{
    use DataKeyTrait;

    /** @var DataContract */
    private $data;

    public function __construct(RuntimeSetContract ...$runtimeContract)
    {
        $this->data = new Data();
        foreach ($runtimeContract as $k => $runtimeSet) {
            $this->data->setKey($runtimeSet->name(), $runtimeSet->value());
        }
        $this->data->setKey('errorReportingLevel', error_reporting());
        $this->config = $this->data->toArray();
    }
}
