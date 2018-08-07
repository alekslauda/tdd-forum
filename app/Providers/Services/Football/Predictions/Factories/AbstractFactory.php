<?php


namespace App\Providers\Services\Football\Predictions\Factories;


/**
 * The common interface of Object Pool
 */
abstract class AbstractFactory
{
    /**
     * @var array
     */
    protected static $instances = array();

    /**
     * Returns an instance of class then was called
     * @return static
     */
    public static function getInstance()
    {
        $className = static::getClassName();
        if( !isset(self::$instances[$className])) {
            self::$instances[$className] = new $className();
        }

        return self::$instances[$className];
    }

    /**
     * Removes an instance of class
     * @return void
     */
    public static function removeInstance()
    {
        $className = static::getClassName();
        if(array_key_exists($className, self::$instances)){
            unset(self::$instances[$className]);
        }
    }

    /**
     * Returns an instance of class
     * @return string
     */
    final protected static function getClassName()
    {
        return get_called_class();
    }

    /**
     * Construct is closed
     */
    protected function __construct() {}

    /**
     * Cloning is prohibited
     */
    final protected function __clone() {}

    /**
     * Serialization is prohibited
     */
    final protected function __sleep() {}

    /**
     * Deserialization is prohibited
     */
    final protected function __wakeup() {}
}
