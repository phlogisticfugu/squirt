<?php
namespace Squirt\Common;

use InvalidArgumentException;
use Closure;

/**
 * This class contains static functions used with Squirt
 */
final class SquirtUtil
{
    /**
     * Validate an input key in a parameter array; testing that
     * the key exists, but not doing any type checking
     * 
     * @param unknown $key
     * @param array $params
     * @throws InvalidArgumentException
     * @return
     */
    public static function validateParam($key, array $params)
    {
        if (array_key_exists($key, $params)) {
            return $params[$key];
        } else {
            throw new InvalidArgumentException('Missing key: ' . $key);
        }
    }
    
    /**
     * Validate an input key in a parameter array
     * to ensure that it exists and is of the proper class
     * 
     * Any errors throw an exception, otherwise the proper
     * value is returned.
     * 
     * This class aims to encapsulate a common pattern used in
     * squirt-compatible classes
     * 
     * @param string $key
     * @param string $class
     * @param array $params
     * @throws InvalidArgumentException
     * @return
     */
    public static function validateParamClass(
        $key,
        $class,
        array $params
    ) {
        
        if (array_key_exists($key, $params)) {
            /*
             * If the value exists then ensure it's type
             */
            if ($params[$key] instanceof $class) {
                return $params[$key];
            } else {
                throw new InvalidArgumentException('value for key:' . $key
                    . ' should be an instance of ' . $class);
            }
            
        } else {
            throw new InvalidArgumentException('Missing key: ' . $key);
        }
    }
    
    /**
     * Validate an input key in a parameter array
     * to ensure that it exists and is of the proper class
     * or return a default value
     * 
     * Any errors throw an exception, otherwise the proper
     * value is returned.
     * 
     * This class aims to encapsulate a common pattern used in
     * squirt-compatible classes
     * 
     * @param string $key
     * @param string $class
     * @param array $params
     * @param unknown $default
     * @throws InvalidArgumentException
     * @return unknown
     */
    public static function validateParamClassWithDefault(
        $key,
        $class,
        array $params,
        $default
    ) {
        if (array_key_exists($key, $params)) {
            /*
             * If the value exists then ensure it's type
            */
            if ($params[$key] instanceof $class) {
                return $params[$key];
            } else {
                throw new InvalidArgumentException('value for key:' . $key
                    . ' should be an instance of ' . $class);
            }
        
        } elseif (($default instanceof Closure) && (! $default instanceof $class)) {
            /*
             * Permit callers to pass a Closure which then does the creation of what will
             * become our default.  But note that we test for that being the type of
             * class we are looking for to begin with, to avoid improper execution
             */
            return $default();
            
        } else {
            return $default;
        }
    }
    
    /**
     * Ensure this is never instantiated
     */
    private function __construct()
    {}    
}

