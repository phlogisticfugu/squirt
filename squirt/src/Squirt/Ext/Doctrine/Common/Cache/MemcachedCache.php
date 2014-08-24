<?php
namespace Squirt\Ext\Doctrine\Common\Cache;

use Doctrine\Common\Cache\MemcachedCache as DoctrineMemcachedCache;
use Squirt\Common\SquirtableInterface;
use Squirt\Common\SquirtUtil;

/**
 * Squirt wrapper for Doctrine class
 */
class MemcachedCache extends DoctrineMemcachedCache implements SquirtableInterface
{
    /**
     * Create a new instance in a manner compatible
     * with the Squirt dependency injection
     * @param array $params
     */
    public static function factory(array $params=array())
    {

        $instance = new static();

        /*
         * To make this work, we must have a memcache instance
         */
        $memcached = SquirtUtil::validateParamClass('memcached', '\Memcached', $params);
        $instance->setMemcached($memcached);

        $namespace =
            SquirtUtil::validateStringParamWithDefault('namespace', $params, 'squirt');
        if (strlen($namespace) > 0) {
            $instance->setNamespace($namespace);
        }

        return $instance;
    }
}
