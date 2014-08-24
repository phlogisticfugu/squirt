<?php
namespace Squirt\ServiceBuilder;

use Squirt\ServiceBuilder\SquirtServiceConfigLoader;

class SquirtServiceConfigLoaderTest extends \PHPUnit_Framework_TestCase
{
    public function testInstantiate()
    {
        $squirtServiceConfigLoader = SquirtServiceConfigLoader::factory();

        $this->assertInstanceOf(
            'Squirt\ServiceBuilder\SquirtServiceConfigLoader',
            $squirtServiceConfigLoader);

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
    public function testListArrayReplacementLoadFile(SquirtServiceConfigLoader $squirtServiceConfigLoader)
    {
        /**
         * When extending parameters from one service to another
         * arrays with all integer keys (lists) are completely replaced
         * instead of recursively replacing individual values, as is done
         * with associative arrays
         */
        $serviceConfig = $squirtServiceConfigLoader->loadConfig(array(
            'services' => array(
                'CONTAINER1' => array(
                    'class' => 'Squirt\Common\Container',
                    'params' => array(
                        'data' => array(1,2,3,4,5),
                        'deep' => array(
                            'color' => 'red'
                        )
                    )
                ),
                'CONTAINER2' => array(
                    'extends' => 'CONTAINER1',
                    'params' => array(
                        'data' => array('a', 'b', 'c'),
                        'deep' => array(
                            'color' => 'blue'
                        )
                    )
                )
            )
        ));
        $this->assertArrayHasKey('CONTAINER2', $serviceConfig);
        $container2Config = $serviceConfig['CONTAINER2'];
        $this->assertEquals(array(
            'class' => 'Squirt\Common\Container',
            'params' => array(
                'data' => array('a', 'b', 'c'),
                'deep' => array(
                    'color' => 'blue'
                )
            )
        ), $container2Config);
    }

    /**
     * @depends testInstantiate
     * @param SquirtServiceConfigLoader $squirtServiceConfigLoader
     */
    public function testLoadFile(SquirtServiceConfigLoader $squirtServiceConfigLoader)
    {

        $configFileName = SQUIRT_TEST_DIR
            . join(DIRECTORY_SEPARATOR, array('', '_config', 'test_config.php'));

        $expected = array(
            'LAMB.TEST' => array(
                'class' => 'Squirt\ServiceBuilder\SquirtServiceConfigLoader'
            ),
            'LAMB.TEST2' => array(
                'class' => 'Squirt\ServiceBuilder\SquirtServiceConfigLoader'
            ),
            'LAMB.TEST3' => array(
                'class' => 'Squirt\ServiceBuilder\SquirtServiceConfigLoader'
            ),
            'WOLF.WORD_CONTAINER' => array(
                'class' => 'Squirt\Common\Container',
                'params' => array(
                    'first' => 'exciting',
                    'second' => 'replaceme:include_config.php',
                    'third' => 'replaceme:include_config.php',
                    'fourth' => 'replaceme:include_config.php'
                )
            ),
            'NUMBER_CONTAINER' => array(
                'class' => 'Squirt\Common\Container',
                'params' => array(
                    'data' => array(1,1,2,3,5,8,13)
                )
            )
        );

        /*
         * Try an initial load (noting that this is stored in the cache)
         */
        $serviceConfig = $squirtServiceConfigLoader->loadFile($configFileName);
        $this->assertTrue(is_array($serviceConfig), 'loadFile returns an array');
        $this->assertEquals($expected, $serviceConfig);

        /*
         * Try a second load, using the cache
         */
        $serviceConfig = $squirtServiceConfigLoader->loadFile($configFileName);
        $this->assertTrue(is_array($serviceConfig), 'loadFile returns an array');
        $this->assertEquals($expected, $serviceConfig);
    }

    /**
     * @depends testInstantiate
     * @param SquirtServiceConfigLoader $squirtServiceConfigLoader
     */
    public function testLoadFileCircular(SquirtServiceConfigLoader $squirtServiceConfigLoader)
    {
        $configFileName = SQUIRT_TEST_DIR
            . join(DIRECTORY_SEPARATOR, array('', '_config', 'circular_reference_config.php'));

        /*
         * Test an invalid config file with a circular reference
         * which should throw a RuntimeException
         */
        $serviceConfig = $squirtServiceConfigLoader->loadFile($configFileName);
        $this->assertTrue(is_array($serviceConfig), 'loadFile returns an array');
    }
}
