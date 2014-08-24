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
         *
         * http://php.net/manual/en/language.oop5.late-static-bindings.php
         *
         * This way the factory instantiates the sub-class and not the parent
         * class, even if this factory is defined in the parent class
         * (or a trait thereof)
         */
        return new static($params);
    }
}
