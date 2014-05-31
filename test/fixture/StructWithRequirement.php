<?php

namespace Spruct\Fixture;

/**
 * @struct.requires description, name
 */
class StructWithRequirement extends \Spruct\Struct
{
    /** @struct.type string  */
    protected $description;

    /** @struct.type string  */
    protected $name;

}
