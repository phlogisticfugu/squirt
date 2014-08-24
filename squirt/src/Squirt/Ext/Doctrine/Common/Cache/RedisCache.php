<?php
namespace Squirt\Ext\Doctrine\Common\Cache;

use Doctrine\Common\Cache\RedisCache as DoctrineRedisCache;
use Squirt\Common\SquirtableInterface;
use Squirt\Common\SquirtUtil;

/**
 * Squirt wrapper for Doctrine class
 */
class RedisCache extends DoctrineRedisCache implements SquirtableInterface
{
    /**
     * Create a new instance in a manner compatible
     * with the Squirt dependency injection
     * @param array $params
     */
    public static function factory(array $params=array())
    {
        $instance = new static();

        $redis = SquirtUtil::validateParamClass('redis', '\Redis', $params);
        $instance->setRedis($redis);

        $namespace =
            SquirtUtil::validateStringParamWithDefault('namespace', $params, 'squirt');
        if (strlen($namespace) > 0) {
            $instance->setNamespace($namespace);
        }

        return $instance;
    }
}
