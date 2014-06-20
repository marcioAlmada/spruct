Spruct
======

[![Build Status](https://travis-ci.org/marcioAlmada/spruct.svg?branch=master)](https://travis-ci.org/marcioAlmada/spruct)
[![Coverage Status](https://coveralls.io/repos/marcioAlmada/spruct/badge.png?branch=master)](https://coveralls.io/r/marcioAlmada/spruct?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/marcioAlmada/spruct/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/marcioAlmada/spruct/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/spruct/spruct/v/stable.svg)](https://packagist.org/packages/spruct/spruct)
[![Total Downloads](https://poser.pugx.org/spruct/spruct/downloads.svg)](https://packagist.org/packages/spruct/spruct)
[![License](https://poser.pugx.org/spruct/spruct/license.svg)](https://packagist.org/packages/spruct/spruct)

Spruct gives you a clean PHP struct implementation with optional strong typed fields.


## Composer Installation

```json
{
  "require": {
    "spruct/spruct": "~1.0"
  }
}
```

Through terminal: `composer require spruct/spruct:~1.0` :8ball:


## Usage

### Declaring Structs

A new struct type can be created by extending the abstract `\Spruct\Struct` class:

```php
/**
 * Struct representing a 2D point
 */
class D2Point extends \Spruct\Struct
{
    /** @struct.type boolean */
    protected $visible = false;

    /** @struct.type float */
    protected $x;

    /** @struct.type float */
    protected $y;
}
```

Fields are declared through protected properties and data types are specified
through the `@struct.type` property annotation:

```php
/**
 * @struct.type <type>
 */
  protected $field;
```

### Valid Type Declarations:

| Type   | Tokens            | Example
|:---    |:---               |:---|
|boolean | `bool`, `boolean` | `/** @struct.type boolean */`
|integer | `integer`, `int`  | `/** @struct.type integer */`
|string  | `string`, `str`   | `/** @struct.type string */`
|double  | `double`, `float` | `/** @struct.type float */`
|array   | `array`           | `/** @struct.type array */`
|Class   | full qualified class name | `/** @struct.type \Some\Existing\Class */`
|regex   | a valid regex expression  | `/** @struct.type #^\w{3}\d+$# */`

### Initializing Structs

Structs can be initialized with a key value array prototype, like this:

```php
$point = new D2Point([
    'x' => 1.0,
    'y' => 2.0
]);
```

### Manipulating Structs

Structs can be manipulated just like a common `\stdClass` instance:

```php
$pointA = new D2Point();
$pointA->visible = false;
$pointA->x = 1.0;
$pointA->y = 1.5;
```

Struct exception messages are pretty self explanatory `\Spruct\StructException`:

```php
$pointB = new D2Point();
$pointB->visible = 'y'; // ! Cannot use string(y) as type float in field D2Point->visible
$pointB->x = 1;         // ! Cannot use integer(1) as type float in field D2Point->x
$pointB->y = [];        // ! Cannot use array as type float in field D2Point->y
```

### Required Fields

You can also declare fields that must not be null using `@struct.requires` class annotation:

```php
/**
 * @struct.requires name, age, role
 */
class Employee extends \Spruct\Struct
{
    /** @struct.type string */
    protected $name;

    /** @struct.type string */
    protected $role;

    /** @struct.type integer */
    protected $age;

    /** @struct.type #^123456\d{8}$# */
    protected $code;
}
```

Required fields are validated during struct initialization.
If required fields are missing, a `\Spruct\StructException` is thrown.

```php
new Employee(['age' => 21]);  // ! Cannot initialize Employee with null ["name", "role"]
```

## Contributing
 
0. Fork [spruct\spruct](https://github.com/marcioAlmada/spruct/fork) and clone
0. Install composer dependencies `$ composer install`
0. Run unit tests with phpunit 4.2+
0. Modify code: correct bug, implement feature
0. Back to step 3

> NOTICE: phpunit 4.2+ is required due to a feature introduced [here](https://github.com/sebastianbergmann/phpunit/issues/1263)

## Copyright

Copyright (c) 2014 Márcio Almada. Distributed under the terms of an MIT-style license. See LICENSE for details.
