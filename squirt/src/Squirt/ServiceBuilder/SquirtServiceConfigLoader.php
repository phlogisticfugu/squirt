<?php
namespace Squirt\ServiceBuilder;

use InvalidArgumentException;
use RuntimeException;
use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\ArrayCache;
use Squirt\Exception\NoSuchServiceException;
use Squirt\Common\SquirtableInterface;
use Squirt\Common\SquirtableLoggingTrait;
use Squirt\Common\SquirtUtil;

/**
 * This class is used to deal with the loading and processing
 * of Squirt configurations from included files or from other configuration
 */
class SquirtServiceConfigLoader implements SquirtableInterface
{
    use SquirtableLoggingTrait;
    
    /**
     * @var \Doctrine\Common\Cache\Cache
     */
    protected $cache;
    
    protected function __construct(array $params)
    {
        
        $this->cache = SquirtUtil::validateParamClassWithDefault(
            'cache',
            'Doctrine\Common\Cache\Cache',
            $params,
            function() {
                return new ArrayCache();
            }
        );
    }
    
    /**
     * Load configuration from an array with keys
     *
     * includes - an array of file names to load from
     * prefix - an optional prefix added to all service names
     * services - an associative array of service configurations
     *
     * @param array $params
     * @param array $loadedFileNameArray
     * @throws InvalidArgumentException
     * @return multitype:Ambigous <multitype:, array>
     */
    public function loadConfig(array $params)
    {
        return $this->actuallyLoadConfig($params, array());
    }
        
    /**
     * Load service configuration from a file
     * 
     * @param string $fileName
     * @param array $loadedFileNameArray
     * @throws InvalidArgumentException
     * @return array
     */
    public function loadFile($fileName)
    {
        return $this->actuallyLoadFile($fileName, array());
    }
    
    protected function actuallyLoadConfig(array $params, array $loadedFileNameArray)
    {
        $serviceConfig = array();
    
        /*
         * Load any includes
        */
        if (isset($params['includes'])) {
            if (is_array($params['includes'])) {
                foreach ($params['includes'] as $fileName) {
    
                    /*
                     * Recursively load configurations, permitting later
                    * includes to override earlier ones, even if the service
                    * names collide
                    */
                    $serviceConfig = array_replace_recursive(
                        $serviceConfig,
                        $this->actuallyLoadFile($fileName, $loadedFileNameArray));
                }
    
            } else {
                throw new InvalidArgumentException('includes must be an array');
            }
        }
    
        /*
         * Apply any prefixing to finalize the names of our services
        */
        if (isset($params['services'])) {
            $serviceConfig = array_replace_recursive(
                $serviceConfig,
                $this->applyPrefix($params));
        }
    
        if (empty($params['prefix'])) {
            $prefix = '';
        } else {
            $prefix = $params['prefix'];
        }
    
        /*
         * Implement any extending of services
        */
        $outServiceConfig = array();
        foreach (array_keys($serviceConfig) as $serviceName) {
            $config = $this->applyServiceExtension($serviceName, $serviceConfig, $prefix);
    
            $outServiceConfig[$serviceName] = $config;
        }
    
        return $outServiceConfig;
    }
    
    protected function actuallyLoadFile($fileName, array $loadedFileNameArray)
    {
        
        /*
         * Handle the case where we might run into infinite recursion
         * if an include file contains a file that includes itself
         * 
         * When we encounter this, at the bottom of the search, return
         * an empty array to stop the infinite loop
         */
        if (false !== array_search($fileName, $loadedFileNameArray)) {
            if (isset($this->logger)) {
                $this->logger->warn('Ignoring circular include file reference: ' . $fileName);
            }
            return array();
        }
        
        /*
         * Use any cached value we can
         */
        if ($this->cache->contains($fileName)) {
            return json_decode($this->cache->fetch($fileName), true);
        }
        
        if (! file_exists($fileName)) {
            throw new InvalidArgumentException('File does not exist: ' . $fileName);
        }
        
        $params = require $fileName;
                
        $loadedFileNameArray[] = $fileName;
        
        $serviceConfig = $this->actuallyLoadConfig($params, $loadedFileNameArray);
        
        /*
         * Cache this serviceConfig
         */
        $this->cache->save($fileName, json_encode($serviceConfig));
        
        return $serviceConfig;
    }
    
    /**
     * Apply any extension on services
     * @param string $serviceName
     * @param array $serviceConfig
     * @param string $prefix
     * @throws NoSuchServiceException
     * @return array
     */
    protected function applyServiceExtension($serviceName, array $serviceConfig, $prefix)
    {
        $config = null;
        
        /*
         * Look for any fully prefixed service to extend first
         */
        if (strlen($prefix) > 0) {
            if (array_key_exists($prefix . '.' . $serviceName, $serviceConfig)) {
                $config = $serviceConfig[$prefix . '.' . $serviceName];
            }
        }
        
        /*
         * If we are at this point the service must exist unprefixed
         */
        if (null === $config) {
            if (array_key_exists($serviceName, $serviceConfig)) {
                $config = $serviceConfig[$serviceName];
                
            } else {
                throw new NoSuchServiceException(
                    'Unable to find serviceName: ' . $serviceName);
            }
        }
        
        /*
         * Apply any configuration from a parent, doing so recursively
         * and ensuring that the child overrides the parent
         */
        if (isset($config['extends'])) {
            $parentService = $config['extends'];
            
            $parentConfig = $this->applyServiceExtension($parentService, $serviceConfig, $prefix);
            
            $config = array_replace_recursive($parentConfig, $config);
        }
        
        return $config;
    }
    
    /**
     * Apply any prefix to the names of services
     * @param array $params
     * @return array $serviceConfig
     */
    protected function applyPrefix(array $params)
    {
        $serviceConfig = array();
        
        if ((! empty($params['prefix'])) && is_string($params['prefix'])) {
            $prefix = $params['prefix'];
            
            foreach ($params['services'] as $serviceName => $config) {
                $serviceConfig[$prefix . '.' . $serviceName] = $config;
            }
            
        } else {
            $serviceConfig = $params['services'];
        }
        
        return $serviceConfig;
    }
}
