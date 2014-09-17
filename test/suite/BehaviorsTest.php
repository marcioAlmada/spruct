<?php

namespace Spruct;

/**
 * BehaviorsTest
 *
 * @group group
 */
class BehaviorsTest extends \PHPUnit_Framework_TestCase
{
    public function testMeta()
    {
        $this->assertSame(Behaviors::meta(), Behaviors::meta());
    }
}
