<?php

namespace Spruct;

abstract class Struct
{
    /**
     * @param array $prototype
     */
    public function __construct(array $prototype = null)
    {
        if ($prototype)
            // hydrates struct with initial values
            array_walk($prototype, function ($value, $property) {
                $this->__set($property, $value);
            });
        // checks for @struct.requires constraints
        Behaviors::validateRequirements($this);
    }

    public function __get($property)
    {
        Behaviors::validateField($this, $property);

        return $this->$property;
    }

    public function __set($property, $value)
    {
        $this->$property = Behaviors::strict($this, $property, $value);
    }
}
