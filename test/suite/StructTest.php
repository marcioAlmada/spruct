<?php

namespace Spruct;

use Spruct\Fixture\Point;
use Spruct\Fixture\DerivedPoint;
use Spruct\Fixture\InvalidTypeStruct;
use Spruct\Fixture\StructWithRequirement;
use Spruct\Fixture\InvalidStructWithRequirement;

/**
 * StructTest
 *
 * @group main
 */
class StructTest extends \PHPUnit_Framework_TestCase
{

    protected $struct;

    public function setup()
    {
        $this->struct = new Point();
    }

    /**
     * @expectedException \Spruct\StructException
     * @expectedExceptionMessage #^((\\?\w)+)->undeclared undefined \(Type \1 has no field undeclared\)$#
     * @expectedExceptionCode 1
     */
    public function testFieldAccessException()
    {
        $this->struct->undeclared;
    }

    /**
     * @dataProvider fieldAssignmentProvider
     */
    public function testFieldAssignment($property, $value, $type)
    {
        $this->struct->$property = $value;
        $this->assertSame($value, $this->struct->$property);
        $this->assertInternalType($type, $this->struct->$property);
    }

    public function fieldAssignmentProvider()
    {
        return [
            ['visible', true,  'boolean'],
            ['visible', false, 'boolean'],
            ['x', 0.1, 'float'],
            ['y', 0.1, 'float'],
            ['group', 10, 'integer'],
            ['description', 'I\'m a point', 'string'],
            ['next', new Point(), 'object'],
            ['previous', new Point(), 'object'],
            ['previous', new DerivedPoint(), 'object'],
            ['name', 'A1', 'string'],
            ['name', 'B12', 'string'],
            ['weak', [], 'array']
        ];
    }

    public function testStructInitialization()
    {
        $point = new Point(["x" => 1.0, "y" => 1.5]);
        $this->assertSame($point->x, 1.0);
        $this->assertSame($point->y, 1.5);
    }

    /**
     * @expectedException \Spruct\StructException
     * @expectedExceptionMessage #^Cannot use ((\\?\w)+)(\(.{0,}\))? as (type|a match for) .+ in field (?1)->\w+$#
     * @expectedExceptionCode 2
     * @dataProvider fieldAssignmentExceptionProvider
     */
    public function testFieldAssignmentException($property, $value)
    {
        $this->struct->$property = $value;
    }

    public function fieldAssignmentExceptionProvider()
    {
        return [
            ['visible', 1],
            ['visible', 'yes'],
            ['visible', ''], // empty string
            ['visible', 'some # random % !    (string) '],
            ['x', 1],
            ['y', 0],
            ['group', 1.0],
            ['description', true],
            ['next', new \stdClass()],
            ['name', 'AA'],
            ['name', 'A1A'],
            ['name', 'a1'],
            ['previous', new \stdClass()],
        ];
    }

    /**
     * @expectedException \Spruct\StructException
     * @expectedExceptionMessage #^Annotated @struct.type must be in \[((").+(?2),?)+\]$#
     * @expectedExceptionCode 3
     */
    public function testInvalidFieldType()
    {
        $this->struct = new InvalidTypeStruct();
        $this->struct->wrong = '';
    }

    /**
     * @expectedException \Spruct\StructException
     * @expectedExceptionMessage #^Cannot initialize ((\\?\w)+) with null \[("\w+",?)+\]?$#
     * @expectedExceptionCode 4
     * @dataProvider missingPropotypeFieldProvider
     */
    public function testFieldRequirement($prototype)
    {
        $this->struct = new StructWithRequirement($prototype);
    }

    public function missingPropotypeFieldProvider()
    {
        return [
            [[]],
            [['description' => 'foo']],
            [['name' => 'bar']],
        ];
    }

    /**
     * @expectedException \Spruct\StructException
     * @expectedExceptionCode 2
     */
    public function testPostInitializationFieldRequirement()
    {
        $this->assertNull($this->struct->x);
        $this->struct->x = null;
    }

    /**
     * @expectedException \Spruct\StructException
     * @expectedExceptionMessage has no field
     * @expectedExceptionCode 1
     */
    public function testInvalidFieldRequirement()
    {
        new InvalidStructWithRequirement(['foo' => 'foo']);
    }

    /**
     * @expectedException \Spruct\StructException
     * @expectedExceptionCode 2
     * @dataProvider invalidPrototypeDataProvider
     */
    public function testRequiredFieldType($prototype)
    {
        $this->struct = new StructWithRequirement($prototype);
    }

    public function invalidPrototypeDataProvider()
    {
        return [
            [['name' => 'valid', 'description' => false]],
            [['name' => 'valid', 'description' => 0]],
            [['name' => 'valid', 'description' => .5]],
            [['name' => true, 'description' => 'valid']],
            [['name' => 1,    'description' => 'valid']],
            [['name' => 1.1,  'description' => 'valid']],
        ];
    }

}
