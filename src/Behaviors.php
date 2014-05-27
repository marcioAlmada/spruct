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

    const TYPE_ERROR  = 'Annotated type must be in %s';

    const REQUIREMENT_ERROR  = 'Cannot initialize %s with a null %s';

    public static function validateField(Struct $struct, $property)
    {
        if( ! property_exists($struct, $property))
            throw new StructException(
                sprintf(self::FIELD_ERROR, get_class($struct), $property));
    }

    public static function strict(Struct $struct, $property, $value)
    {
        static::validateField($struct, $property);
        $type = static::getPropertyType($struct, $property);
        if (null !== $type) {
            static::validatePropertyType($type, $property, $value);
        }

        return $value;
    }

    private static function getPropertyType(Struct $struct, $property)
    {
        $type = Meta::getPropertyAnnotations($struct, $property)->get('struct.type');

        return static::findTypeToken($type);
    }

    public static function findTypeToken($type)
    {
        if ( null !== $type && is_string($type)) {
            if(false !== strpos($type, '\\')) return $type;
            foreach (static::$types as $token => $types) {
                if (in_array($type, $types)) return $token;
            }

            throw new StructException(
                sprintf(self::TYPE_ERROR, json_encode(static::getTypeTokens())));
        }
    }

    public static function getTypeTokens()
    {
        return array_keys(static::$types);
    }

    public static function getRequirements(Struct $struct)
    {
        $requirements = Meta::getClassAnnotations($struct)->get('struct.requires');
        if (is_string($requirements))
            $requirements = explode(',', preg_replace('#\s#', '', $requirements));
        else
            $requirements = [];

        return $requirements;
    }

    public static function validatePropertyType($expected, $property, $value)
    {
        $type = gettype($value);
        
        if('object' === $type) {
            if (0 !== strpos($expected, '\\')) {
                $expected =  '\\' . $expected;
            }
        }

        if($value instanceof $expected || $type === $expected) {
            return true;
        }

        if (is_scalar($value)) {
            $type .= "({$value})";
        } else if (is_object($value)) {
            $type .= '(' . get_class($value) . ')';
        }

        throw new StructException(
            sprintf(self::VAL_ERROR, $type, $expected, $property));
    }

    public static function validateRequirements(Struct $struct)
    {
        $requirements = Behaviors::getRequirements($struct);
        array_walk($requirements, function ($field) use ($struct) {
            if ($struct->__get($field) === null)
                throw new StructException(
                    sprintf(self::REQUIREMENT_ERROR, get_class($struct), $field));
        });
    }
}
