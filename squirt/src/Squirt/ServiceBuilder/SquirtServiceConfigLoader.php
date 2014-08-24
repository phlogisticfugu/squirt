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
use Squirt\ServiceBuilder\ServiceBuilderUtil;

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

    /**
     * The number of seconds that we can cache a single configuration
     * @var integer
     */
    protected $cacheLifetimeSeconds;

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

        $this->cacheLifetimeSeconds =
            SquirtUtil::validateNumericParamWithDefault(
                'cacheLifetimeSeconds', $params, 0);
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
                    $serviceConfig = ServiceBuilderUtil::mergeConfig(
                        $serviceConfig,
                        $this->actuallyLoadFile($fileName, $loadedFileNameArray));
                }

            } else {
                throw new InvalidArgumentException('includes must be an array');
            }
        }

        if (empty($params['prefix'])) {
            $prefix = '';
        } else {
            $prefix = $params['prefix'];
        }

        /*
         * Process the services defined in this current configuration
         */
        if (array_key_exists('services', $params)) {

            $services = $params['services'];

            /*
             * Expand any aliases on the services
             */
            $services = $this->applyServiceAliases($services);

            /*
             * Apply any prefixing to the names of our services
             * but only the ones in this file
             */
            if (strlen($prefix) > 0) {
                $services = $this->applyPrefix($services, $prefix);
            }

            /*
             * Merge these services in with what we are preparing for output
             */
            $serviceConfig = ServiceBuilderUtil::mergeConfig(
                $serviceConfig,
                $services);
        }

        /*
         * Implement any extending of services
         */
        $outServiceConfig = array();
        $serviceNameArray = array_keys($serviceConfig);
        foreach ($serviceNameArray as $serviceName) {
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
        $cachedConfigJSON = $this->cache->fetch($fileName);
        if (false !== $cachedConfigJSON) {
            return json_decode($cachedConfigJSON, true);
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
        $this->cache->save($fileName, json_encode($serviceConfig), $this->cacheLifetimeSeconds);

        return $serviceConfig;
    }

    protected function applyServiceAliases(array $serviceConfig)
    {
        $serviceNameArray = array_keys($serviceConfig);

        foreach ($serviceNameArray as $serviceName) {
            $config = $serviceConfig[$serviceName];

            if (isset($config['aliases']) && is_array($config['aliases'])) {
                $aliases = $config['aliases'];

                /*
                 * Delete the aliases after we grabbed them
                 * so that they dont make their way into processed and optimized
                 * values
                 */
                unset($config['aliases']);

                /*
                 * Make copies of the configuration under each alias
                 */
                foreach($aliases as $alias) {
                    $serviceConfig[$alias] = $config;
                }
            }
        }

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
            unset($config['extends']);

            $parentConfig = $this->applyServiceExtension($parentService, $serviceConfig, $prefix);

            $config = ServiceBuilderUtil::mergeConfig($parentConfig, $config);
        }

        return $config;
    }

    /**
     * Apply any prefix to the names of services
     * @param array $params
     * @return array $serviceConfig
     */
    protected function applyPrefix(array $services, $prefix)
    {
        $outServices = array();

        foreach ($services as $serviceName => $config) {
            $outServices[$prefix . '.' . $serviceName] = $config;
        }

        return $outServices;
    }
}
