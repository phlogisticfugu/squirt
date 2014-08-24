<?php
namespace Squirt\Ext\Doctrine\Common\Cache;

use Doctrine\Common\Cache\MongoDBCache as DoctrineMongoDBCache;
use Squirt\Common\SquirtableInterface;
use Squirt\Common\SquirtUtil;

/**
 * Squirt wrapper for Doctrine class
 */
class MongoDBCache extends DoctrineMongoDBCache implements SquirtableInterface
{
    /**
     * Create a new instance in a manner compatible
     * with the Squirt dependency injection
     * @param array $params
     */
    public static function factory(array $params=array())
    {
        $collection = SquirtUtil::validateParamClass('collection', '\MongoCollection', $params);

        $instance = new static($collection);

        $namespace =
            SquirtUtil::validateStringParamWithDefault('namespace', $params, 'squirt');
        if (strlen($namespace) > 0) {
            $instance->setNamespace($namespace);
        }

        return $instance;
    }
}
