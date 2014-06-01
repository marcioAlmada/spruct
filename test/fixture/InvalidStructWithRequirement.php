<?php

namespace Spruct\Fixture;

/**
 * A struct with requirements that can't be satisfied
 * 
 * @struct.requires foo, bar, baz
 */
class InvalidStructWithRequirement extends \Spruct\Struct
{
    /** @struct.type string  */
    protected $foo;

}
