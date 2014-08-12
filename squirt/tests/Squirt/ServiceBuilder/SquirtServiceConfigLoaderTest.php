<?php
namespace Squirt\ServiceBuilder;

use Squirt\ServiceBuilder\SquirtServiceConfigLoader;

class SquirtServiceConfigLoaderTest extends \PHPUnit_Framework_TestCase
{
    public function testInstantiate()
    {
        $squirtServiceConfigLoader = SquirtServiceConfigLoader::factory();
        
        $this->assertInstanceOf('Squirt\ServiceBuilder\SquirtServiceConfigLoader', $squirtServiceConfigLoader);
        
        return $squirtServiceConfigLoader;
    }
    
    /**
     * @depends testInstantiate
     * @param SquirtServiceConfigLoader $squirtServiceConfigLoader
     */
    public function testEmptyLoadConfig(SquirtServiceConfigLoader $squirtServiceConfigLoader)
    {
        $serviceConfig = $squirtServiceConfigLoader->loadConfig(array());
        $this->assertTrue(is_array($serviceConfig), 'loadConfig returns an array');
        $this->assertEquals(0, count($serviceConfig), 'empty serviceConfig');
    }
    
    /**
     * @depends testInstantiate
     * @param SquirtServiceConfigLoader $squirtServiceConfigLoader
     */
    public function testSimpleLoadConfig(SquirtServiceConfigLoader $squirtServiceConfigLoader)
    {
        /*
         * Test plain services, using out own class as an example
         */
        $serviceConfig = $squirtServiceConfigLoader->loadConfig(array(
            'services' => array(
                'SERVICE_CONFIG_LOADER' => array(
                    'class' => 'Squirt\ServiceBuilder\SquirtServiceConfigLoader'
                )
            )
        ));
        $this->assertTrue(is_array($serviceConfig), 'loadConfig returns an array');
        $this->assertArrayHasKey('SERVICE_CONFIG_LOADER', $serviceConfig);
        $this->assertEquals('Squirt\ServiceBuilder\SquirtServiceConfigLoader',
            $serviceConfig['SERVICE_CONFIG_LOADER']['class']);
    }
    
    /**
     * @depends testInstantiate
     * @param SquirtServiceConfigLoader $squirtServiceConfigLoader
     */
    public function testPrefixedLoadConfig(SquirtServiceConfigLoader $squirtServiceConfigLoader)
    {
        $serviceConfig = $squirtServiceConfigLoader->loadConfig(array(
            'prefix' => 'SQUIRT',
            'services' => array(
                'SERVICE_CONFIG_LOADER' => array(
                    'class' => 'Squirt\ServiceBuilder\SquirtServiceConfigLoader'
                )
            )
        ));
        $this->assertTrue(is_array($serviceConfig), 'loadConfig returns an array');
        $this->assertArrayHasKey('SQUIRT.SERVICE_CONFIG_LOADER', $serviceConfig);
        $this->assertEquals('Squirt\ServiceBuilder\SquirtServiceConfigLoader',
            $serviceConfig['SQUIRT.SERVICE_CONFIG_LOADER']['class']);
    }
    
    /**
     * @depends testInstantiate
     * @param SquirtServiceConfigLoader $squirtServiceConfigLoader
     */
    public function testExtendsLoadConfig(SquirtServiceConfigLoader $squirtServiceConfigLoader)
    {
        /*
         * Test a complex setup, with a prefix and double extension
         */
        $serviceConfig = $squirtServiceConfigLoader->loadConfig(array(
            'prefix' => 'SQUIRT',
            'services' => array(
                'abstract_service' => array(
                    'params' => array(
                        'color' => 'red'
                    )
                ),
                'SERVICE_CONFIG_LOADER' => array(
                    'extends' => 'abstract_service',
                    'class' => 'Squirt\ServiceBuilder\SquirtServiceConfigLoader'
                ),
                'SERVICE_CONFIG_LOADER2' => array(
                    'extends' => 'SQUIRT.SERVICE_CONFIG_LOADER',
                    'params' => array(
                        'color' => 'blue',
                        'hat' => 'fedora'
                    )
                )
            )
        ));
        $this->assertTrue(is_array($serviceConfig), 'loadConfig returns an array');
        $this->assertArrayHasKey('SQUIRT.SERVICE_CONFIG_LOADER', $serviceConfig);
        
        /*
         * SERVICE_CONFIG_LOADER extends abstract_service and so gets its properties
         * as well
         */
        $this->assertEquals(array(
            'extends' => 'abstract_service',
            'params' => array(
                'color' => 'red'
            ),
            'class' => 'Squirt\ServiceBuilder\SquirtServiceConfigLoader'
        ), $serviceConfig['SQUIRT.SERVICE_CONFIG_LOADER']);
        
        /*
         * SERVICE_CONFIG_LOADER2 extends both SERVICE_CONFIG_LOADER and abstract_service
         * but uses the fully qualified version of the name for SERVICE_CONFIG_LOADER
         * and overrides the color
         */
        $this->assertEquals(array(
            'extends' => 'SQUIRT.SERVICE_CONFIG_LOADER',
            'params' => array(
                'color' => 'blue',
                'hat' => 'fedora'
            ),
            'class' => 'Squirt\ServiceBuilder\SquirtServiceConfigLoader'
        ), $serviceConfig['SQUIRT.SERVICE_CONFIG_LOADER2']);
    }
    
    /**
     * @depends testInstantiate
     * @param SquirtServiceConfigLoader $squirtServiceConfigLoader
     */
    public function testLoadFile(SquirtServiceConfigLoader $squirtServiceConfigLoader)
    {
        /*
         * Try an initial load (noting that this is stored in the cache)
         */
        $serviceConfig = $squirtServiceConfigLoader->loadFile('./tests/_config/test_config.php');
        $this->assertTrue(is_array($serviceConfig), 'loadFile returns an array');
        $this->assertEquals(array(
            'TEST' => array(
                'class' => 'Squirt\ServiceBuilder\SquirtServiceConfigLoader'
            )
        ), $serviceConfig);
        
        /*
         * Try a second load, using the cache
         */
        $serviceConfig = $squirtServiceConfigLoader->loadFile('./tests/_config/test_config.php');
        $this->assertTrue(is_array($serviceConfig), 'loadFile returns an array');
        $this->assertEquals(array(
            'TEST' => array(
                'class' => 'Squirt\ServiceBuilder\SquirtServiceConfigLoader'
            )
        ), $serviceConfig);
    }
    
    /**
     * @depends testInstantiate
     * @param SquirtServiceConfigLoader $squirtServiceConfigLoader
     */
    public function testLoadFileCircular(SquirtServiceConfigLoader $squirtServiceConfigLoader)
    {
        /*
         * Test an invalid config file with a circular reference
         * which should throw a RuntimeException
         */
        $serviceConfig = $squirtServiceConfigLoader->loadFile(
            './tests/_config/circular_reference_config.php');
        $this->assertTrue(is_array($serviceConfig), 'loadFile returns an array');
    }
}
