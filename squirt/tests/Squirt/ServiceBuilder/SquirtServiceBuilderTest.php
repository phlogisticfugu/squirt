<?php
namespace Squirt\ServiceBuilder;

use Squirt\ServiceBuilder\SquirtServiceBuilder;

class SquirtServiceBuilderTest extends \PHPUnit_Framework_TestCase
{
    
    public function testInstantiate()
    {
        $squirtServiceBuilder = SquirtServiceBuilder::factory();
        
        $this->assertInstanceOf('Squirt\ServiceBuilder\SquirtServiceBuilder', $squirtServiceBuilder);
        
        return $squirtServiceBuilder;
    }
    
    /**
     * @depends testInstantiate
     * @param SquirtServiceBuilder $squirtServiceBuilder
     * @expectedException \Squirt\Exception\NoSuchServiceException
     */
    public function testNoConfigGet(SquirtServiceBuilder $squirtServiceBuilder)
    {
        /*
         * requesting a service which doesn't exist should throw an exception
         */
        $squirtServiceBuilder->get('DOES_NOT_EXIST');
        $this->fail('Should not be able to get a service that does not exist');
    }
    
    public function testFileConfigGet()
    {
        $squirtServiceBuilder = SquirtServiceBuilder::factory(array(
            'fileName' => SQUIRT_TEST_DIR . '/_config/service_config.php',
            'config' => array(
                'services' => array(
                    'CONTAINER' => array(
                        'class' => 'Squirt\Common\Container'
                        ,'params' => array(
                            'word' => 'phlogiston'
                        )
                    ),
                    'WORD_CONTAINER' => array(
                        'params' => array(
                            'third' => 'majesty',
                            'fourth' => 'replaceme:config'
                        )
                    )
                )
            )
        ));
        
        /*
         * Test that overrides cascade in the following order from least to highest priority:
         * 
         * files included inside config file
         * config file
         * SqurtServiceBuilder constructor config
         * instance overrides passed to get() method
         * 
         */
        $wordContainer = $squirtServiceBuilder->get('WORD_CONTAINER', array(
            'fourth' => 'card'
        ));
        $this->assertInstanceOf('Squirt\Common\Container', $wordContainer);
        $this->assertEquals('exciting', $wordContainer['first'], 'reads from include_config.php');
        $this->assertEquals('cabinet', $wordContainer['second'], 'reads from service_config.php');
        $this->assertEquals('majesty', $wordContainer['third'], 'reads from constructor config');
        $this->assertEquals('card', $wordContainer['fourth'], 'reads from get overrides');
    }
    
    public function testSimpleGet()
    {
        $squirtServiceBuilder = SquirtServiceBuilder::factory(array(
            'config' => array(
                'services' => array(
                    'CONTAINER' => array(
                        'class' => 'Squirt\Common\Container'
                        ,'params' => array(
                            'word' => 'phlogiston'
                        )
                    )
                )
            )
        ));
        
        $container = $squirtServiceBuilder->get('CONTAINER', array(
            'color' => 'red'
        ));
        
        $this->assertInstanceOf('Squirt\Common\Container', $container);
        $this->assertEquals('phlogiston', $container['word'], 'sets parameters in config');
        $this->assertEquals('red', $container['color'], 'sets parameters in get');
    }
    
    /**
     * Test that services can extend each other, replacing parameters
     * of the same name, even at a deep level
     */
    public function testExtendsGet()
    {
        $squirtServiceBuilder = SquirtServiceBuilder::factory(array(
            'config' => array(
                'services' => array(
                    'CONTAINER_1' => array(
                        'class' => 'Squirt\Common\Container'
                        ,'params' => array(
                            'word' => 'phlogiston',
                            'foo' => 'bar',
                            'deep' => array(
                                'mood' => 'sad'
                            )
                        )
                    ),
                    'CONTAINER_2' => array(
                        'extends' => 'CONTAINER_1',
                        'params' => array(
                            'foo' => 'baz',
                            'deep' => array(
                                'mood' => 'happy'
                            )
                        )
                    )
                )
            )
        ));
        
        $container1 = $squirtServiceBuilder->get('CONTAINER_1');
        $container2 = $squirtServiceBuilder->get('CONTAINER_2');
        
        $this->assertInstanceOf('Squirt\Common\Container', $container1);
        $this->assertInstanceOf('Squirt\Common\Container', $container2);
        
        /*
         * Test that container1 has values as configured
         */
        $this->assertEquals('phlogiston', $container1['word']);
        $this->assertEquals('bar', $container1['foo']);
        $this->assertEquals(array('mood' => 'sad'), $container1['deep']);
        
        /*
         * Test that container2 overrides parameters, but otherwise keeps
         * values from container1
         */
        $this->assertEquals('phlogiston', $container2['word']);
        $this->assertEquals('baz', $container2['foo']);
        $this->assertEquals(array('mood' => 'happy'), $container2['deep']);
        
        /*
         * Test that container2 can be further overridden at a deep level
         * from the get line
         */
        $container2 = $squirtServiceBuilder->get('CONTAINER_2', array(
            'deep' => array(
                'mood' => 'mysterious',
                'animal' => 'manta'
            )
        ));
        $this->assertEquals(array(
            'mood' => 'mysterious',
            'animal' => 'manta'
        ), $container2['deep']);
    }
    
    public function testAliasedGet()
    {
        $squirtServiceBuilder = SquirtServiceBuilder::factory(array(
            'config' => array(
                'services' => array(
                    'THING' => array(
                        'class' => 'Squirt\Common\Container',
                        'aliases' => array(
                            'CONTAINER',
                            'BOX'
                        ),
                        'params' => array(
                            'foo' => 'phlogiston'
                        )
                    )
                )
            )
        ));
        
        $instance = $squirtServiceBuilder->get('THING');
        $this->assertInstanceOf('Squirt\Common\Container', $instance);
        
        $instance = $squirtServiceBuilder->get('CONTAINER');
        $this->assertInstanceOf('Squirt\Common\Container', $instance);
        
        $instance = $squirtServiceBuilder->get('BOX');
        $this->assertInstanceOf('Squirt\Common\Container', $instance);
    }
    
    public function testCachedGet()
    {
        $squirtServiceBuilder = SquirtServiceBuilder::factory(array(
            'config' => array(
                'services' => array(
                    'CONTAINER' => array(
                        'class' => 'Squirt\Common\Container'
                        ,'params' => array()
                    )
                )
            )
        ));
        
        $instance1 = $squirtServiceBuilder->get('CONTAINER');
        $instance2 = $squirtServiceBuilder->get('CONTAINER', array());
        $instance3 = $squirtServiceBuilder->get('CONTAINER', null, false);
        $instance4 = $squirtServiceBuilder->get('CONTAINER');
        $instance5 = $squirtServiceBuilder->get('CONTAINER', null, true);
        
        /*
         * Because they were both obtained with no arguments, these
         * instances should be the same
         */
        $this->assertSame($instance1, $instance4);
        
        /*
         * Because instance5 was instantiated with the defauls
         * it should also use the cached version of the instance1
         */
        $this->assertSame($instance1, $instance5);
        
        $this->assertNotSame($instance1, $instance2);
        $this->assertNotSame($instance1, $instance3);
    }
    
    /**
     * @expectedException \LogicException
     */
    public function testInfiniteRecursiveService()
    {
        /*
         * Test a service which requires itself, which should
         * result in an exception being thrown, and not an infinite loop
         */
        $squirtServiceBuilder = SquirtServiceBuilder::factory(array(
            'config' => array(
                'services' => array(
                    'CONTAINER1' => array(
                        'class' => 'Squirt\Common\Container'
                        ,'params' => array(
                            '{CONTAINER2}'
                        )
                    ),
                    'CONTAINER2' => array(
                        'class' => 'Squirt\Common\Container'
                        ,'params' => array(
                            '{CONTAINER1}'
                        )
                    )
                )
            )
        ));
        
        $container1 = $squirtServiceBuilder->get('CONTAINER1');
        
        $this->fail('Should not get past a bad config');
    }
    
    public function testSimpleGetConfig()
    {
        $squirtServiceBuilder = SquirtServiceBuilder::factory(array(
            'config' => array(
                'services' => array(
                    'CONTAINER' => array(
                        'class' => 'Squirt\Common\Container'
                        ,'params' => array(
                            'word' => 'phlogiston'
                        )
                    )
                )
            )
        ));
        $this->assertEquals(array(
            'class' => 'Squirt\Common\Container',
            'params' => array(
                'word' => 'phlogiston'
            )
        ), $squirtServiceBuilder->getConfig('CONTAINER'));
    }
}
