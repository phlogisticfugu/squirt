<?php
namespace Squirt\Common;

/**
 * This trait implements the SquirtableInterface in this most simple form
 */
trait SquirtableTrait
{
    /**
     * Simple static factory used to create a new instance
     * in a manner compatible with Squirt dependency injection
     * @param array $params
     */
    public static function factory(array $params=array())
    {
        /*
         * Use late static binding to create an instance of the
         * proper concrete class
         */
        return new static($params);
    }
}
