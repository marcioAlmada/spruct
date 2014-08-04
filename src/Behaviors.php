<?php

namespace Spruct;

use Minime\Annotations\Facade as Meta;
use RegexGuard\Factory as RegexGuard;

abstract class Behaviors extends Struct
{
    protected static $types = [
        'boolean' => ['boolean', 'bool'],
        'double'  => ['double',  'float'],
        'integer' => ['integer', 'int'],
        'string'  => ['string',  'str'],
        'array'   => ['array'],
        'object'  => ['object'],
        '<class>' => [],
        '<regex>' => [],
    ];

    const FIELD_ERROR = '%1$s->%2$s undefined (Type %1$s has no field %2$s)';

    const VAL_ERROR   = 'Cannot use %s as type %s in field %s';

    const VAL_MATCH_ERROR  = 'Cannot use %s as a match for %s in field %s';

    const TYPE_ERROR  = 'Annotated @struct.type must be in %s';

    const REQUIREMENT_ERROR  = 'Cannot initialize %s with null %s';

    const ANNOTATION_TYPE = 'struct.type';

    const ANNOTATION_REQUIRES = 'struct.requires';

    protected static function validateField(Struct $struct, $property)
    {
        if ( ! property_exists($struct, $property)) {
            throw new StructException(
                sprintf(self::FIELD_ERROR, get_class($struct), $property), 1);
        }
    }

    protected static function strict(Struct $struct, $property, $value)
    {
        static::validateField($struct, $property);
        $type = static::getPropertyType($struct, $property);
        $property = get_class($struct) . '->' . $property;
        if (null !== $type) {
            static::validatePropertyType($type, $property, $value);
        }

        return $value;
    }

    protected static function getPropertyType(Struct $struct, $property)
    {
        $type = Meta::getPropertyAnnotations($struct, $property)->get(self::ANNOTATION_TYPE);

        return static::findTypeToken($type);
    }

    protected static function findTypeToken($type)
    {
        if ( null !== $type && is_string($type)) {
            $regexGuard = RegexGuard::getGuard();
            if (class_exists($type) || false !== $regexGuard->isRegexValid($type)) {
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

    protected static function getTypeTokens()
    {
        return array_keys(static::$types);
    }

    protected static function getRequirements(Struct $struct)
    {
        $requirements = Meta::getClassAnnotations($struct)->get(self::ANNOTATION_REQUIRES);
        if (is_string($requirements)) {
            $requirements = array_map('trim', explode(',', $requirements));
        } else {
            $requirements = [];
        }

        return $requirements;
    }

    protected static function validatePropertyType($expected, $property, $value)
    {
        $type = gettype($value);
        $regexGuard = RegexGuard::getGuard()->throwOnError(false);

        if ($type === $expected ||
            $value instanceof $expected ||
            (is_string($value) && 1 === $regexGuard->match($expected, $value))) {
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

    protected static function validateRequirements(Struct $struct)
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
