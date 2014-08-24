<?php
namespace Squirt\Ext\Doctrine\Common\Cache;

use Doctrine\Common\Cache\RiakCache as DoctrineRiakCache;
use Squirt\Common\SquirtableInterface;
use Squirt\Common\SquirtUtil;

/**
 * Squirt wrapper for Doctrine class
 */
class RiakCache extends DoctrineRiakCache implements SquirtableInterface
{
    /**
     * Create a new instance in a manner compatible
     * with the Squirt dependency injection
     * @param array $params
     */
    public static function factory(array $params=array())
    {
        $bucket = SquirtUtil::validateParamClass('bucket', 'Riak\Bucket', $params);

        $instance = new static($bucket);

        $namespace =
            SquirtUtil::validateStringParamWithDefault('namespace', $params, 'squirt');
        if (strlen($namespace) > 0) {
            $instance->setNamespace($namespace);
        }

        return $instance;
    }
}
