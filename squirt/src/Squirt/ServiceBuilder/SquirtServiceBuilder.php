<?php
namespace Squirt\ServiceBuilder;

use \InvalidArgumentException;
use \RuntimeException;
use Squirt\Exception\NoSuchServiceException;
use Squirt\Common\SquirtableInterface;
use Squirt\Common\SquirtableTrait;
use Squirt\ServiceBuilder\SquirtServiceConfigLoader;

/**
 * This class is the main interface to Squirt.  A SquirtServiceBuilder
 * provides the main access to configured services inside the application code
 * 
 * Note that a SquirtServiceBuilder can itself be configured and injected
 * with dependancies and configuration
 */
class SquirtServiceBuilder implements SquirtableInterface
{
    use SquirtableTrait;
    
    /**
     * @var \Squirt\ServiceBuilder\SquirtServiceConfigLoader
     */
    protected $squirtServiceConfigLoader;
    
    /**
     * Configurations for services
     * @var array
     */
    protected $serviceConfig = array();
    
    /**
     * Cache of services which have already been instantiated
     * @var array
    */
    protected $instantiatedNameServiceCache = array();
    
    protected function __construct(array $params)
    {
        if (isset($params['squirtServiceConfigLoader'])) {
            if ($params['squirtServiceConfigLoader'] instanceof SquirtServiceConfigLoader) {
                $this->squirtServiceConfigLoader = $params['squirtServiceConfigLoader'];
            } else {
                throw new InvalidArgumentException('invalid squirtServiceConfigLoader');
            }
        } else {
            $this->squirtServiceConfigLoader = SquirtServiceConfigLoader::factory($params);
        }
        
        $this->serviceConfig = array();
        
        /*
         * Load any file based configuration
         */
        if (array_key_exists('fileName', $params)) {
            $serviceConfig = $this->squirtServiceConfigLoader->loadFile($params['fileName']);
            $this->serviceConfig = array_replace_recursive($this->serviceConfig, $serviceConfig);
        }
        
        /*
         * Load any literal configuration passed
         */
        if (array_key_exists('config', $params)) {
            $serviceConfig = $this->squirtServiceConfigLoader->loadConfig($params['config']);
            $this->serviceConfig = array_replace_recursive($this->serviceConfig, $serviceConfig);
        }
    }
    
    /**
     * Get an instance of the service with a given name
     * 
     * with some optionally overridable instance parameters
     * and a flag to control caching
     * 
     * @param string $serviceName
     * @param array|null $instanceParams
     * @param boolean $allowCache
     * @throws NoSuchServiceException
     * @throws RuntimeException
     */
    public function get($serviceName, $instanceParams=null, $allowCache=true)
    {
        /*
         * Use a cached service if possible
         */
        if ($allowCache && (null === $instanceParams)) {
            if (isset($this->instantiatedNameServiceCache[$serviceName])) {
                return $this->instantiatedNameServiceCache[$serviceName];
            }
        }
        
        $config = $this->getConfig($serviceName, $instanceParams);
        $params = $config['params'];
        
        /*
         * Lookup any references to other services in the parameters
         * for this service
         */
        $params = $this->processParams($params, $allowCache);
        
        /*
         * Actually construct the service
         */
        $class = $config['class'];
        $service = $class::factory($params);
        
        /*
         * Do some caching as appropriate
         */
        if ($allowCache && (null === $instanceParams)) {
            $this->instantiatedNameServiceCache[$serviceName] = $service;
        }
        
        return $service;
    }
    
    /**
     * Get the configuration that would be used to instantiate a service
     * but without doing any actual instantiations
     * 
     * @param string $serviceName
     * @param array|null $instanceParams
     * @throws NoSuchServiceException
     * @return array
     */
    public function getConfig($serviceName, $instanceParams=null)
    {
        if (! isset($this->serviceConfig[$serviceName])) {
            throw new NoSuchServiceException('No such service: ' . $serviceName);
        }
        
        $config = $this->serviceConfig[$serviceName];
        
        /*
         * Safely get and alter some parameters to use in instantiating
         */
        if (isset($config['params'])) {
            $params = $config['params'];
        } else {
            $params = array();
        }
        
        if (is_array($instanceParams)) {
            $params = array_replace_recursive($params, $instanceParams);
        }
        
        $config['params'] = $params;
        
        return $config;
    }
    
    /**
     * Process parameters from a configuration, looking up any references
     * to other services and instantiating them
     * @param array $params
     * @param boolean $allowCache
     */
    protected function processParams(array $params, $allowCache)
    {
        
        $out = array_map(function($value) use ($allowCache) {
            if (is_string($value)
                && preg_match('/^{([a-zA-Z0-9_\\.\\-]+)}$/', $value, $matches)) {
                
                /*
                 * Resolve any named service into it's instance
                 */
                $value = $this->get($matches[1], null, $allowCache);
            
            } elseif (is_array($value)) {
                $value = $this->processParams($value, $allowCache);    
            }
            
            return $value;
            
        }, $params);
        
        return $out;
    }
}
