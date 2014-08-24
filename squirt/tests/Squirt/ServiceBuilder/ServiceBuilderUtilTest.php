<?php
namespace Squirt\ServiceBuilder;

use Squirt\ServiceBuilder\ServiceBuilderUtil;

class ServiceBuilderUtilTest extends \PHPUnit_Framework_TestCase
{
    public function testMergeConfig()
    {
        $result = ServiceBuilderUtil::mergeConfig(array(), array());
        $this->assertEquals(array(), $result);
        
        $result = ServiceBuilderUtil::mergeConfig(array('foo' => 'bar'), array());
        $this->assertEquals(array('foo' => 'bar'), $result);
        
        $result = ServiceBuilderUtil::mergeConfig(array(), array('foo' => 'bar'));
        $this->assertEquals(array('foo' => 'bar'), $result);
        
        $result = ServiceBuilderUtil::mergeConfig(array('foo' => 'baz!!'), array('foo' => 'bar'));
        $this->assertEquals(array('foo' => 'bar'), $result);
    }
    
    public function testMergeConfigMismatch()
    {
        $result = ServiceBuilderUtil::mergeConfig(
            array('foo' => array('color' => 'red')),
            array('foo' => 'bar'));
        $this->assertEquals(array('foo' => 'bar'), $result);
        
        $result = ServiceBuilderUtil::mergeConfig(
            array('foo' => 'override'),
            array('foo' => array('color' => 'red')));
        $this->assertEquals(array('foo' => array('color' => 'red')), $result);
    }
    
    public function testMergeConfigRecursive()
    {
        $result = ServiceBuilderUtil::mergeConfig(
            array('layer1' => array(
                'layer2' => array(
                    'other' => 1,
                    'layer3' => array('color' => 'red')
                )
            )),
            array('layer1' => array(
                'layer2' => array(
                    'layer3' => array('color' => 'blue')
                )
            )));
        $this->assertEquals(array(
            'layer1' => array(
                'layer2' => array(
                    'other' => 1,
                    'layer3' => array(
                        'color' => 'blue'
                    )
                )
            )
        ), $result);
    }
    
    public function testMergeConfigList()
    {
        /*
         * When a configuration is comprised solely of integer ids, then it
         * is a list, and we should let the child have precedence
         */
        $result = ServiceBuilderUtil::mergeConfig(array(1,2,3,4,5,6), array('a','b','c'));
        $this->assertEquals(array('a','b','c'), $result);
    }
}
