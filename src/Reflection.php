<?php

/**
 * This file is part of the Reflection package
 *
 * (c) InnovationGroup
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace FivePercent\Component\Reflection;

use Doctrine\Common\Annotations\Reader;

/**
 * Reflection helper
 *
 * @author Vitaliy Zhuk <zhuk2205@gmail.com>
 */
final class Reflection
{
    const TYPE_CLASS        = 0b00000001;
    const TYPE_OBJECT       = 0b00000010;

    /**
     * @var array|\ReflectionClass[]
     */
    private static $classReflections = array();

    /**
     * @var array|\ReflectionObject[]
     */
    private static $objectReflections = array();

    /**
     * Disable constructor
     */
    private function __construct()
    {
    }

    /**
     * Load class reflection
     *
     * @param string|object $class
     *
     * @return \ReflectionClass
     */
    public static function loadClassReflection($class)
    {
        if (is_object($class)) {
            $class = get_class($class);
        }

        if (isset(self::$classReflections[$class])) {
            return self::$classReflections[$class];
        }

        $reflection = new \ReflectionClass($class);

        self::$classReflections[$class] = $reflection;

        return $reflection;
    }

    /**
     * Load method reflection
     *
     * @param string|object $class
     * @param string        $method
     *
     * @return \ReflectionMethod
     */
    public static function loadMethodReflection($class, $method)
    {
        $reflectionClass = self::loadClassReflection($class);

        return $reflectionClass->getMethod($method);
    }

    /**
     * Load object reflection
     *
     * @param object $object
     *
     * @return \ReflectionObject
     */
    public static function loadObjectReflection($object)
    {
        if (!is_object($object)) {
            throw new \InvalidArgumentException(sprintf(
                'Could not load reflection for non object. Given: "%s".',
                gettype($object)
            ));
        }

        $hash = spl_object_hash($object);

        if (isset(self::$objectReflections[$hash])) {
            return self::$objectReflections[$hash];
        }

        $reflection = new \ReflectionObject($object);

        self::$objectReflections[$hash] = $reflection;

        return $reflection;
    }

    /**
     * Get called method from abstract reflection function
     *
     * @param \ReflectionFunctionAbstract $method
     * @param bool                        $closureInfo
     *
     * @return string
     */
    public static function getCalledMethod(\ReflectionFunctionAbstract $method, $closureInfo = true)
    {
        if ($method->isClosure()) {
            if ($closureInfo) {
                return sprintf(
                    'Closure [%s:%d]',
                    $method->getFileName(),
                    $method->getStartLine()
                );
            }

            return 'Closure';
        }

        if ($method instanceof \ReflectionMethod) {
            return sprintf(
                '%s::%s',
                $method->getDeclaringClass()->getName(),
                $method->getName()
            );
        }

        return $method->getName();
    }

    /**
     * Get class properties
     *
     * @param string $class
     * @param bool   $inParents
     * @param int    $filter
     *
     * @return \ReflectionProperty[]
     */
    public static function getClassProperties($class, $inParents = false, $filter = null)
    {
        if ($filter === null) {
            $filter = \ReflectionProperty::IS_PRIVATE
                | \ReflectionProperty::IS_PROTECTED
                | \ReflectionProperty::IS_PUBLIC;
        }

        $reflection = self::loadClassReflection($class);

        if (!$inParents) {
            return $reflection->getProperties($filter);
        }

        $properties = [];

        do {
            $properties = array_merge(
                $reflection->getProperties($filter),
                $properties
            );
        } while ($reflection = $reflection->getParentClass());

        return $properties;
    }

    /**
     * Set value to property
     *
     * @param object|string $object
     * @param string        $property
     * @param mixed  $value
     */
    public static function setPropertyValue($object, $property, $value)
    {
        $ref = self::loadClassReflection($object);

        $refProperty = $ref->getProperty($property);

        if (!$refProperty->isPublic()) {
            $refProperty->setAccessible(true);
        }

        if ($refProperty->isStatic()) {
            $refProperty->setValue($value);
        } else {
            $refProperty->setValue($object, $value);
        }
    }

    /**
     * Set value to properties
     *
     * @param object|string $object
     * @param array         $properties
     */
    public static function setPropertiesValue($object, array $properties)
    {
        foreach ($properties as $name => $value) {
            self::setPropertyValue($object, $name, $value);
        }
    }

    /**
     * Get property value
     *
     * @param object|string $object
     * @param string        $property
     *
     * @return mixed
     */
    public static function getPropertyValue($object, $property)
    {
        $ref = self::loadClassReflection($object);

        $refProperty = $ref->getProperty($property);

        if (!$refProperty->isPublic()) {
            $refProperty->setAccessible(true);
        }

        if ($refProperty->isStatic()) {
            $value = $refProperty->getValue();
        } else {
            $value = $refProperty->getValue($object);
        }

        return $value;
    }

    /**
     * Load class annotations
     *
     * @param Reader        $reader
     * @param string|object $class
     * @param bool          $inParents
     *
     * @return array
     */
    public static function loadClassAnnotations(Reader $reader, $class, $inParents = false)
    {
        $reflection = self::loadClassReflection($class);

        if (!$inParents) {
            return $reader->getClassAnnotations($reflection);
        }

        $annotations = [];

        do {
            $classAnnotations = $reader->getClassAnnotations($reflection);

            foreach ($classAnnotations as $classAnnotation) {
                if (!isset($annotations[get_class($classAnnotation)])) {
                    $annotations[get_class($classAnnotation)] = $classAnnotation;
                }
            }
        } while ($reflection = $reflection->getParentClass());

        return array_values($annotations);
    }

    /**
     * Clear internal storage
     *
     * @param int $mode
     */
    public static function clear($mode = null)
    {
        if (is_numeric($mode)) {
            $mode = self::TYPE_CLASS | self::TYPE_OBJECT;
        }

        if ($mode & self::TYPE_CLASS) {
            self::$classReflections = array();
        }

        if ($mode & self::TYPE_OBJECT) {
            self::$objectReflections = array();
        }
    }
}
