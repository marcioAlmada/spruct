<?php

namespace Spruct;

use Minime\Annotations\Facade as Meta;

class Behaviors
{
    protected static $types = [
        'boolean' => ['boolean', 'bool'],
        'double'  => ['double',  'float'],
        'integer' => ['integer', 'int'],
        'string'  => ['string',  'str'],
        'array'   => ['array'],
        'object'  => ['object'],
    ];

    const FIELD_ERROR = '%1$s->%2$s undefined (Type %1$s has no field %2$s)';

    const VAL_ERROR   = 'Cannot use %s as type %s in field %s';

    const TYPE_ERROR  = 'Annotated @struct.type must must be in %s';

    const REQUIREMENT_ERROR  = 'Cannot initialize %s with null %s';

    const ANNOTATION_TYPE = 'struct.type';

    const ANNOTATION_REQUIRES = 'struct.requires';

    public static function validateField(Struct $struct, $property)
    {
        if ( ! property_exists($struct, $property)) {
            throw new StructException(
                sprintf(self::FIELD_ERROR, get_class($struct), $property), 1);
        }
    }

    public static function strict(Struct $struct, $property, $value)
    {
        static::validateField($struct, $property);
        $type = static::getPropertyType($struct, $property);
        $property = get_class($struct) . '->' . $property;
        if (null !== $type) {
            static::validatePropertyType($type, $property, $value);
        }

        return $value;
    }

    public static function getPropertyType(Struct $struct, $property)
    {
        $type = Meta::getPropertyAnnotations($struct, $property)->get(self::ANNOTATION_TYPE);

        return static::findTypeToken($type);
    }

    public static function findTypeToken($type)
    {
        if ( null !== $type && is_string($type)) {
            if (false !== strpos($type, '\\')) {
                return $type;
            }
            foreach (static::$types as $token => $types) {
                if (in_array($type, $types)) {
                    return $token;
                }
            }

            throw new StructException(
                sprintf(self::TYPE_ERROR, json_encode(static::getTypeTokens())), 3);
        }
    }

    public static function getTypeTokens()
    {
        return array_keys(static::$types);
    }

    public static function getRequirements(Struct $struct)
    {
        $requirements = Meta::getClassAnnotations($struct)->get(self::ANNOTATION_REQUIRES);
        if (is_string($requirements)) {
            $requirements = array_map('trim', explode(',', $requirements));
        } else {
            $requirements = [];
        }

        return $requirements;
    }

    public static function validatePropertyType($expected, $property, $value)
    {
        $type = gettype($value);

        if ('object' === $type) {
            if (0 !== strpos($expected, '\\')) {
                $expected =  '\\' . $expected;
            }
        }

        if ($value instanceof $expected || $type === $expected) {
            return true;
        }

        if (is_scalar($value)) {
            $type .= "({$value})";
        } elseif (is_object($value)) {
            $type .= '(' . get_class($value) . ')';
        }

        throw new StructException(
            sprintf(self::VAL_ERROR, $type, $expected, $property), 2);
    }

    public static function validateRequirements(Struct $struct)
    {
        $requirements = Behaviors::getRequirements($struct);
        $missing = [];
        array_walk($requirements, function ($field) use ($struct, &$missing) {
            if ($struct->__get($field) === null) {
                $missing[] = $field;
            }
        });
        if ($missing) {
            throw new StructException(
                sprintf(self::REQUIREMENT_ERROR, get_class($struct), json_encode($missing)), 4);
        }
    }
}
