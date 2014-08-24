<?php
namespace Squirt\Common;

use Squirt\Common\Container;

class ContainerTest extends \PHPUnit_Framework_TestCase
{
    public function testInstantiate()
    {
        $container = Container::factory(array(
            'color' => 'blue'
            ,'size' => 10
        ));

        $this->assertInstanceOf('Squirt\Common\Container', $container);

        return $container;
    }

    /**
     * @depends testInstantiate
     * @param Container $container
     */
    public function testArrayAccess(Container $container)
    {
        $this->assertEquals('blue', $container['color']);
        $this->assertEquals(10, $container['size']);

        $container['fish'] = 'fugu';
        $this->assertEquals('fugu', $container['fish']);
    }
}
