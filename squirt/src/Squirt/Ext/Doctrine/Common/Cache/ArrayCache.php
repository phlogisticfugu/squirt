<?php
namespace Squirt\Ext\Doctrine\Common\Cache;

use InvalidArgumentException;
use Doctrine\Common\Cache\ArrayCache as DoctrineArrayCache;
use Squirt\Common\SquirtableInterface;
use Squirt\Common\SquirtableTrait;

/**
 * Provide a Squirt-compatible instance of the Doctrine ArrayCache
 */
class ArrayCache extends DoctrineArrayCache implements SquirtableInterface
{
    use SquirtableTrait;
    
    protected function __construct(array $params)
    {
        if (array_key_exists('namespace', $params)) {
            if (is_string($params['namespace'])) {
                $this->setNamespace($params['namespace']);
            } else {
                throw new InvalidArgumentException('namespace must be a string');
            }
        }
    }
}
