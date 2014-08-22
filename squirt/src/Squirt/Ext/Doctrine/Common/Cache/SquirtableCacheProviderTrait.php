<?php
namespace Squirt\Ext\Doctrine\Common\Cache;

use Squirt\Common\SquirtUtil;

/**
 * This trait is meant for insertion into basic Doctrine Cache instances
 * when building out Squirt-compatible interfaces for them
 */
trait SquirtableCacheProviderTrait
{
    /**
     * Create a new instance in a manner compatible
     * with the Squirt dependency injection
     * @param array $params
     */
    public static function factory(array $params=array())
    {
        $instance = new static();
        
        $namespace =
            SquirtUtil::validateStringParamWithDefault('namespace', $params, 'squirt');
        if (strlen($namespace) > 0) {
            $instance->setNamespace($namespace);
        }
        
        return $instance;
    }
}
