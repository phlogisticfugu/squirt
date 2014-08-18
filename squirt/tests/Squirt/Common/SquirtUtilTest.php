<?php
namespace Squirt\Common;

use DateTime;
use DateTimeZone;
use Squirt\Common\SquirtUtil;
use Squirt\Common\Container;

class SquirtUtilTest extends \PHPUnit_Framework_TestCase
{
    
    public function testValidateParam()
    {
        /*
         * Test normal usage
         */
        $result = SquirtUtil::validateParam('color', array('color' => 'navy'));
        $this->assertEquals('navy', $result);
    }
    
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testValidateParamMissing()
    {
        $result = SquirtUtil::validateParam('color', array('flavor' => 'spicy'));
        $this->fail('Should not get here');
    }
    
    public function testValidateParamClass()
    {
        /*
         * Test normal usage
         */
        $result = SquirtUtil::validateParamClass('date','\DateTime', array(
            'date' => new DateTime('2014-08-01', new DateTimeZone('UTC'))
        ));
        $this->assertInstanceOf('\DateTime', $result);
    }
    
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testValidateParamClassMissing()
    {
        /*
         * The name of the key is mismatched, so this should
         * throw an InvalidArgumentException
         */
        $result = SquirtUtil::validateParamClass('date','\DateTime', array(
            'DATE' => new DateTime('2014-08-01', new DateTimeZone('UTC'))
        ));
        $this->fail('Should not get here');
    }
    
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testValidateParamClassBadClass()
    {
        /*
         * The value does not match the class we expected
         * so this should throw an InvalidArgumentException
         */
        $result = SquirtUtil::validateParamClass('date','\DateTime', array(
            'date' => new DateTimeZone('UTC')
        ));
        $this->fail('Should not get here');
    }
    
    public function testValidateParamClassWithDefault()
    {
        $result = SquirtUtil::validateParamClassWithDefault(
            'basket',
            'Squirt\Common\Container',
            array(),
            Container::factory(array('color' => 'blue')));
        $this->assertInstanceOf('Squirt\Common\Container', $result);
    }
    
    public function testValidateParamClassWithDefaultClosure()
    {
        /*
         * When one is concerned about excessive instantiation
         * one can pass a closure as the default that then returns
         * a default value itself
         */
        $result = SquirtUtil::validateParamClassWithDefault(
            'container',
            'Squirt\Common\Container',
            array(),
            function() {
                return Container::factory(array('color' => 'blue'));    
            }
        );
        $this->assertInstanceOf('Squirt\Common\Container', $result);
    }
}

