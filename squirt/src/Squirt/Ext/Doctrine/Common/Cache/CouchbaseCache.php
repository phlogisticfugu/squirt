<?php
namespace Squirt\Ext\Doctrine\Common\Cache;

use Doctrine\Common\Cache\CouchbaseCache as DoctrineCouchbaseCache;
use Squirt\Common\SquirtableInterface;
use Squirt\Common\SquirtUtil;

/**
 * Squirt wrapper for Doctrine class
 */
class CouchbaseCache extends DoctrineCouchbaseCache implements SquirtableInterface
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
         * To make this work, we must have a couchbase instance
         */
        $couchbase = SquirtUtil::validateParamClass('couchbase', '\Couchbase', $params);
        $instance->setCouchbase($couchbase);
        
        $namespace =
            SquirtUtil::validateStringParamWithDefault('namespace', $params, 'squirt');
        if (strlen($namespace) > 0) {
            $instance->setNamespace($namespace);
        }
        
        return $instance;
    }
}
