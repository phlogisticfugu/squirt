<?php
namespace Squirt\Ext\Doctrine\Common\Cache;

use InvalidArgumentException;
use Doctrine\Common\Cache\PhpFileCache as DoctrinePhpFileCache;
use Squirt\Common\SquirtableInterface;
use Squirt\Common\SquirtUtil;

/**
 * Provide a Squirt-compatible instance of the Doctrine PhpFileCache
 */
class PhpFileCache extends DoctrinePhpFileCache implements SquirtableInterface
{

    /**
     * Create a new instance in a manner compatible
     * with the Squirt dependency injection
     * @param array $params
     */
    public static function factory(array $params = array())
    {
        /*
         * The directory is required to instantiate our PhpFileCache
         * so test for it explicitly
         */
        $directory = SquirtUtil::validateStringParam('directory', $params);
        
        /*
         * The Cache namespace is optional
         */
        $namespace = SquirtUtil::validateStringParamWithDefault('namespace', $params, '');
        
        /*
         * The extension on the filenames is overridable
         */
        $extension = SquirtUtil::validateStringParamWithDefault('extension', $params, null);
        
        /*
         * Create and configure our instance
         */
        $instance = new static($directory, $extension);
        
        if (strlen($namespace) > 0) {
            $instance->setNamespace($namespace);
        }
        
        return $instance;
    }
}
