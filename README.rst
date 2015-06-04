.. title:: Reflection helper for PHP

=================
Reflection Helper
=================

This package add helper methods for work with `PHP Reflection <http://php.net/manual/en/book.reflection.php>`_

Installation
------------

Add **FivePercent/Reflection** in your composer.json:

.. code-block:: json

    {
        "require": {
            "fivepercent/reflection": "~1.0"
        }
    }


Now tell composer to download the library by running the command:


.. code-block:: bash

    $ php composer.phar update fivepercent/reflection


Basic usage
-----------

The class ``FivePercent\Component\Reflection\Reflection`` is final, and can not use as object.
All methods in this class statically.

Examples for work with this package:

.. code-block:: php

    use FivePercent\Component\Reflection\Reflection

    // Get class reflection
    $reflection = Reflection::loadClassReflection('MyClass');

    // Get method reflection
    $reflection = Reflection::loadMethodReflection('MyClass', 'myMethod');

    // Get object reflection
    $object = new MyClass();
    $reflection = Reflection::loadObjectReflection($object);

    // Get all properties from class
    $properties = Reflection::getClassProperties('MyClass');

    // Get all properties from class with gets from parents classes
    $properties = Reflection::getClassProperties('MyClass', true);

    // Get only private properties from class
    $properties = Reflection::getClassProperties('MyClass', false, \ReflectionProperty::IS_PRIVATE);

    // Set value to property in object
    $object = new MyClass();
    Reflection::setPropertyValue($object, 'myProperty', 'FooBar');

    // Get value from property
    $object = new MyClass();
    $value = Reflection::getPropertyValue($object, 'myProperty');
