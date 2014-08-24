<?php
namespace Squirt\ServiceBuilder;

use InvalidArgumentException;
use RuntimeException;
use LogicException;
use Squirt\Exception\NoSuchServiceException;
use Squirt\Common\SquirtableInterface;
use Squirt\Common\SquirtableTrait;
use Squirt\Common\SquirtUtil;
use Squirt\ServiceBuilder\SquirtServiceConfigLoader;
use Squirt\ServiceBuilder\ServiceBuilderUtil;

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

        $this->squirtServiceConfigLoader = SquirtUtil::validateParamClassWithDefault(
            'squirtServiceConfigLoader',
            'Squirt\ServiceBuilder\SquirtServiceConfigLoader',
            $params,
            function() use ($params) {
                /*
                 * Pass through parameters as these two classes are tightly
                 * coupled and can use each other's configurations
                 */
                return SquirtServiceConfigLoader::factory($params);
            }
        );

        /*
         * Load any file based configuration
         */
        if (array_key_exists('fileName', $params)) {
            $serviceConfig = $this->squirtServiceConfigLoader->loadFile($params['fileName']);
            $this->serviceConfig = ServiceBuilderUtil::mergeConfig(
                $this->serviceConfig, $serviceConfig);
        }

        /*
         * Load any literal configuration passed
         */
        if (array_key_exists('config', $params)) {
            $serviceConfig = $this->squirtServiceConfigLoader->loadConfig($params['config']);
            $this->serviceConfig = ServiceBuilderUtil::mergeConfig(
                $this->serviceConfig, $serviceConfig);
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
        return $this->actuallyGet($serviceName, $instanceParams, $allowCache, array());
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

        /*
         * Apply overrides passed at the time of instantiation
         */
        if (is_array($instanceParams)) {
            $params = ServiceBuilderUtil::mergeConfig($params, $instanceParams);
        }

        $config['params'] = $params;

        return $config;
    }

    /**
     * Execute the actual code to get a service instance, but with some extra state maintained
     * in the calls
     */
    protected function actuallyGet(
        $serviceName,
        $instanceParams,
        $allowCache,
        array $requestedServiceNameSet
    ) {
        $useCache = ($allowCache && (null === $instanceParams));

        /*
         * Use a cached service if possible
         */
        if ($useCache) {
            if (array_key_exists($serviceName, $this->instantiatedNameServiceCache)) {
                return $this->instantiatedNameServiceCache[$serviceName];
            }
        }

        /*
         * No cached instance already exists, so start building one
         */
        $requestedServiceNameSet[$serviceName] = true;

        $config = $this->getConfig($serviceName, $instanceParams);
        $params = $config['params'];

        /*
         * Lookup any references to other services in the parameters
         * for this service
         */
        $params = $this->processParams($params, $allowCache, $requestedServiceNameSet);

        /*
         * Actually construct the service
         */
        $class = $config['class'];
        $service = $class::factory($params);

        /*
         * Do some caching as appropriate
         */
        if ($useCache) {
            $this->instantiatedNameServiceCache[$serviceName] = $service;
        }

        return $service;
    }

    /**
     * Process parameters from a configuration, looking up any references
     * to other services and instantiating them
     * @param array $params
     * @param boolean $allowCache
     * @param array $requestedServiceNameSet
     */
    protected function processParams(array $params, $allowCache, array $requestedServiceNameSet)
    {

        $out = array_map(function($value) use ($allowCache, $requestedServiceNameSet) {
            if (is_string($value)
                && preg_match('/^{([a-zA-Z0-9_\\.\\-]+)}$/', $value, $matches)) {

                /*
                 * Resolve any named service into it's instance
                 */
                $serviceName = $matches[1];

                /*
                 * Prevent infinite recursion
                 */
                if (isset($requestedServiceNameSet[$serviceName])) {
                    throw new LogicException('Invalid infinite recursion. serviceName:' . $serviceName);
                }

                $value = $this->actuallyGet($serviceName, null, $allowCache, $requestedServiceNameSet);

            } elseif (is_array($value)) {
                $value = $this->processParams($value, $allowCache, $requestedServiceNameSet);
            }

            return $value;

        }, $params);

        return $out;
    }
}
