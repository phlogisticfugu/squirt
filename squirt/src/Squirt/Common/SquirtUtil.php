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
     * Validate a parameter and require that it's value be a string
     * @param string $key
     * @param array $params
     * @throws InvalidArgumentException
     * @return string
     */
    public static function validateStringParam($key, array $params)
    {
        if (array_key_exists($key, $params)) {
            $value = $params[$key];
            
            if (is_string($value)) {
                return $value;
            } else {
                throw new InvalidArgumentException(
                    'Expected value to be string for key: ' . $key);
            }
            
        } else {
            throw new InvalidArgumentException('Missing key: ' . $key);
        }
    }
    
    /**
     * Validate that a key, if set, is a string, otherwise return the default
     * @param string $key
     * @param array $params
     * @param string $default
     * @throws InvalidArgumentException
     * @return string
     */
    public static function validateStringParamWithDefault($key, array $params, $default)
    {
        if (array_key_exists($key, $params)) {
            $value = $params[$key];
        
            if (is_string($value)) {
                return $value;
            } else {
                throw new InvalidArgumentException(
                    'Expected value to be string for key: ' . $key);
            }
        
        } else {
            return $default;
        }
    }
    
    /**
     * Require that a parameter be set and be numeric
     * @param string $key
     * @param array $params
     * @throws InvalidArgumentException
     * @return unknown
     */
    public static function validateNumericParam($key, array $params)
    {
        if (array_key_exists($key, $params)) {
            $value = $params[$key];
        
            if (is_numeric($var)) {
                return $value;
            } else {
                throw new InvalidArgumentException(
                    'Expected value to be numeric for key: ' . $key);
            }
        
        } else {
            throw new InvalidArgumentException('Missing key: ' . $key);
        }
    }
    
    /**
     * Validate that if a parameter is set, that it be numeric
     * otherwise use a default value
     * @param string $key
     * @param array $params
     * @param number $default
     * @throws InvalidArgumentException
     * @return number
     */
    public static function validateNumericParamWithDefault($key, array $params, $default)
    {
        if (array_key_exists($key, $params)) {
            $value = $params[$key];
        
            if (is_numeric($var)) {
                return $value;
            } else {
                throw new InvalidArgumentException(
                    'Expected value to be numeric for key: ' . $key);
            }
        
        } else {
            return $default;
        }
    }
    
    /**
     * Expect that a parameter value be set and be a boolean
     * @param string $key
     * @param array $params
     * @throws InvalidArgumentException
     * @return boolean
     */
    public static function validateBooleanParam($key, array $params)
    {
        if (array_key_exists($key, $params)) {
            $value = $params[$key];
        
            if (is_bool($var)) {
                return $value;
            } else {
                throw new InvalidArgumentException(
                    'Expected value to be boolean for key: ' . $key);
            }
        
        } else {
            throw new InvalidArgumentException('Missing key: ' . $key);
        }
    }
    
    /**
     * Require that a parameter, if set, be a boolean
     * otherwise use a default value
     * @param string $key
     * @param array $params
     * @param boolean $default
     * @throws InvalidArgumentException
     * @return boolean
     */
    public static function validateBooleanParamWithDefault($key, array $params, $default)
    {
        if (array_key_exists($key, $params)) {
            $value = $params[$key];
        
            if (is_bool($var)) {
                return $value;
            } else {
                throw new InvalidArgumentException(
                    'Expected value to be boolean for key: ' . $key);
            }
        
        } else {
            return $default;
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

