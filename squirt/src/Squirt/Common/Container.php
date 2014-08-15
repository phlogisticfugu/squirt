<?php
namespace Squirt\Common;

use Squirt\Common\SquirtableInterface;
use Squirt\Common\SquirtableTrait;

/**
 * This is a simple container of key/value pairs
 * which is setup to be configurable via Squirt
 */
class Container implements SquirtableInterface, \ArrayAccess, \IteratorAggregate, \Countable
{
    use SquirtableTrait;
    
    /**
     * @var array
     */
    protected $data;
    
    protected function __construct(array $params)
    {
        $this->data = $params;
    }
    
    /**
     * (non-PHPdoc)
     * @see ArrayAccess::offsetExists()
     */
    public function offsetExists($offet)
    {
        return array_key_exists($offset, $this->data);
    }
    
    /**
     * (non-PHPdoc)
     * @see ArrayAccess::offsetGet()
     */
    public function offsetGet($offset)
    {
        return $this->data[$offset];
    }
    
    /**
     * (non-PHPdoc)
     * @see ArrayAccess::offsetSet()
     */
    public function offsetSet($offset, $value)
    {
        $this->data[$offset] = $value;
    }
    
    /**
     * (non-PHPdoc)
     * @see ArrayAccess::offsetUnset()
     */
    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }
    
    public function getIterator()
    {
        return new \ArrayIterator($this->data);
    }
    
    public function count()
    {
        return count($this->data);
    }
}
