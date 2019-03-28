<?php

declare (strict_types = 1);

namespace PB\Tests\Extension\Scrapbook\Tag\Library;

/**
 * Static class with common reflection logic.
 *
 * @author Wojciech Brzeziński <wojciech.brzezinski@smartint.pl>
 */
final class Reflection
{
    /**
     * Calls method of the given object and returns its result.
     *
     * @param object $object
     * @param string $name
     * @param array $args
     *
     * @return mixed
     *
     * @throws \ReflectionException
     */
    public static function callMethod($object, string $name, array $args)
    {
        $method = static::getReflectionMethod(get_class($object), $name);
        $method->setAccessible(true);

        return $method->invoke($object, ...$args);
    }

    /**
     * Calls static method and returns its result.
     *
     * @param string $class
     * @param string $name
     * @param array  $args
     *
     * @return mixed
     *
     * @throws \ReflectionException
     */
    public static function callStaticMethod(string $class, string $name, array $args)
    {
        $method = static::getReflectionMethod($class, $name);
        $method->setAccessible(true);

        return $method->invoke(null, ...$args);
    }

    /**
     * Returns property value of the given object.
     *
     * @param object $object
     * @param string $name
     *
     * @return mixed
     *
     * @throws \ReflectionException
     */
    public static function getPropertyValue($object, string $name)
    {
        $property = static::getReflectionProperty(get_class($object), $name);
        $property->setAccessible(true);

        return $property->getValue($object);
    }

    /**
     * Sets property value of the given object.
     *
     * @param object $object
     * @param string $name
     * @param mixed  $value
     *
     * @return void
     *
     * @throws \ReflectionException
     */
    public static function setPropertyValue($object, string $name, $value): void
    {
        $property = static::getReflectionProperty(get_class($object), $name);
        $property->setAccessible(true);

        $property->setValue($object, $value);
    }

    /**
     * Returns new reflection of given class.
     *
     * @param string $class
     *
     * @return \ReflectionClass
     *
     * @throws \ReflectionException
     */
    public static function getReflectionClass(string $class): \ReflectionClass
    {
        return new \ReflectionClass($class);
    }

    /**
     * Returns method reflection of the given class.
     *
     * @param string $class
     * @param string $name
     *
     * @return \ReflectionMethod
     *
     * @throws \ReflectionException
     */
    public static function getReflectionMethod(string $class, string $name): \ReflectionMethod
    {
        return static::getReflectionClass($class)->getMethod($name);
    }

    /**
     * Returns property reflection of the given class.
     *
     * @param string $class
     * @param string $name
     *
     * @return \ReflectionProperty
     *
     * @throws \ReflectionException
     */
    public static function getReflectionProperty(string $class, string $name): \ReflectionProperty
    {
        return static::getReflectionClass($class)->getProperty($name);
    }
}
