<?php
namespace Squirt\Ext\Doctrine\Common\Cache;

use Squirt\Common\SquirtUtil;

trait SquirtableFileCacheTrait
{
    /**
     * Create a new instance in a manner compatible
     * with the Squirt dependency injection
     * @param array $params
     */
    public static function factory(array $params = array())
    {    
        /*
         * Create and configure our instance
         */
        $directory = SquirtUtil::validateStringParam('directory', $params);
        $extension = SquirtUtil::validateStringParamWithDefault('extension', $params, null);
        $instance = new static($directory, $extension);
    
        $namespace = SquirtUtil::validateStringParamWithDefault('namespace', $params, 'squirt');
        if (strlen($namespace) > 0) {
            $instance->setNamespace($namespace);
        }
    
        return $instance;
    }
}
