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

    public function testValidateStringParam()
    {
        $result = SquirtUtil::validateStringParam('color', array('color' => 'navy'));
        $this->assertEquals('navy', $result);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testValidateStringParamInvalid()
    {
        $result = SquirtUtil::validateStringParam('color', array('color' => null));
        $this->fail('Should not get here');
    }

    public function testValidateStringParamWithDefault()
    {
        $result = SquirtUtil::validateStringParamWithDefault('color', array('color' => 'navy'), 'red');
        $this->assertEquals('navy', $result);

        $result = SquirtUtil::validateStringParamWithDefault('color', array(), 'red');
        $this->assertEquals('red', $result);
    }

    public function testValidateNumericParam()
    {
        $result = SquirtUtil::validateNumericParam('num', array('num' => '5'));
        $this->assertEquals('5', $result);

        $result = SquirtUtil::validateNumericParam('num', array('num' => 6));
        $this->assertEquals(6, $result);

        $result = SquirtUtil::validateNumericParam('num', array('num' => '0.0'));
        $this->assertEquals('0.0', $result);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testValidateNumericParamInvalid()
    {
        $result = SquirtUtil::validateNumericParam('num', array('num' => 'butterfly'));
        $this->fail('Should not get here');
    }

    public function testValidateNumericParamWithDefault()
    {
        $result = SquirtUtil::validateNumericParamWithDefault('num', array('num' => '5'), 3);
        $this->assertEquals('5', $result);

        $result = SquirtUtil::validateNumericParamWithDefault('num', array('num' => 6), 3);
        $this->assertEquals(6, $result);

        $result = SquirtUtil::validateNumericParamWithDefault('num', array(), 3);
        $this->assertEquals(3, $result);
    }

    public function testValidateBooleanParam()
    {
        $result = SquirtUtil::validateBooleanParam('flag', array('flag' => true));
        $this->assertSame(true, $result);

        $result = SquirtUtil::validateBooleanParam('flag', array('flag' => false));
        $this->assertSame(false, $result);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testValidateBooleanParamInvalid()
    {
        $result = SquirtUtil::validateBooleanParam('flag', array('flag' => null));
        $this->fail('Should not get here');
    }

    public function testValidateBooleanParamWithDefault()
    {
        $result = SquirtUtil::validateBooleanParamWithDefault('flag', array('flag' => true), false);
        $this->assertSame(true, $result);

        $result = SquirtUtil::validateBooleanParamWithDefault('flag', array('other' => true), false);
        $this->assertSame(false, $result);
    }

    public function testValidateArrayParam()
    {
        $result = SquirtUtil::validateArrayParam('set', array('set' => array(1,2,3)));
        $this->assertEquals(array(1,2,3), $result);

        $result = SquirtUtil::validateArrayParam('set', array('set' => array()));
        $this->assertEquals(array(), $result);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testValidateArrayParamInvalid()
    {
        $result = SquirtUtil::validateArrayParam('set', array('set' => 0));
        $this->fail('Should not get here');
    }

    public function testValidateArrayParamWithDefault()
    {
        $result = SquirtUtil::validateArrayParamWithDefault('set', array('set' => array(1,2,3)), array());
        $this->assertEquals(array(1,2,3), $result);

        $result = SquirtUtil::validateArrayParamWithDefault('set', array(), array(5));
        $this->assertEquals(array(5), $result);
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

