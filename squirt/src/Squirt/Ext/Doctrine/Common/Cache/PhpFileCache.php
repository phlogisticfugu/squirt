<?php
namespace Squirt\Ext\Doctrine\Common\Cache;

use InvalidArgumentException;
use Doctrine\Common\Cache\PhpFileCache as DoctrinePhpFileCache;
use Squirt\Common\SquirtableInterface;

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
        if (empty($params['directory'])) {
            throw new InvalidArgumentException('Missing directory');
        }
        $directory = $params['directory'];
        
        /*
         * The Cache namespace is optional
         */
        if (empty($params['namespace'])) {
            $namespace = '';
        } else {
            $namespace = $params['namespace'];
        }
        
        /*
         * The extension on the filenames is overridable
         */
        if (empty($params['extension'])) {
            $extension = null;
        } else {
            $extension = $params['extension'];
        }
        
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
