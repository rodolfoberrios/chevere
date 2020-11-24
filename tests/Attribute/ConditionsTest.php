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

namespace Chevere\Tests\Attribute;

use Chevere\Components\Attribute\Condition;
use Chevere\Components\Attribute\Conditions;
use Chevere\Exceptions\Core\OutOfBoundsException;
use Chevere\Exceptions\Core\OverflowException;
use PHPUnit\Framework\TestCase;

final class ConditionsTest extends TestCase
{
    public function testEmpty(): void
    {
        $conditions = new Conditions;
        $this->assertCount(0, $conditions);
        $this->assertFalse($conditions->contains(ConditionsTestCondition::class));
        $this->expectException(OutOfBoundsException::class);
        $conditions->get(ConditionsTestCondition::class);
    }

    public function testAdded(): void
    {
        $condition = new ConditionsTestCondition(false);
        $conditions = (new Conditions)->withAdded($condition);
        $this->assertCount(1, $conditions);
        $this->assertTrue($conditions->contains(ConditionsTestCondition::class));
        $this->assertEquals($condition, $conditions->get(ConditionsTestCondition::class));
        $this->expectException(OverflowException::class);
        $conditions->withAdded($condition);
    }

    public function testModify(): void
    {
        $condition = new ConditionsTestCondition(false);
        $conditions = (new Conditions)->withAdded($condition);
        $conditionModify = new ConditionsTestCondition(true);
        $conditions = $conditions->withModify($conditionModify);
        $this->assertCount(1, $conditions);
        $this->assertTrue($conditions->contains(ConditionsTestCondition::class));
        $this->assertEquals($conditionModify, $conditions->get(ConditionsTestCondition::class));
        $this->expectException(OutOfBoundsException::class);
        $conditions->withModify(new ConditionsTestConditionAlt(false));
    }
}

final class ConditionsTestCondition extends Condition
{
}

final class ConditionsTestConditionAlt extends Condition
{
}