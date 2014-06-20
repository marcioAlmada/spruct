<?php

namespace Spruct\Fixture;

class Point extends \Spruct\Struct
{
    /** @struct.type boolean */
    protected $visible;

    /** @struct.type float */
    protected $x;

    /** @struct.type double */
    protected $y;

    /** @struct.type integer */
    protected $group;

    /** @struct.type string */
    protected $description;

    /** @struct.type \Spruct\Fixture\Point */
    protected $next;

    /** @struct.type Spruct\Fixture\Point */
    protected $previous;

    /** @struct.type #^[A-Z]\d+$# */
    protected $name;

    /** Any data type fits here */
    protected $weak;

}
