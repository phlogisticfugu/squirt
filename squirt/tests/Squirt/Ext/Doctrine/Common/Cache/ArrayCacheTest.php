<?php
namespace Squirt\Ext\Doctrine\Common\Cache;

use Squirt\Ext\Doctrine\Common\Cache\ArrayCache;

class ArrayCacheTest extends \PHPUnit_Framework_TestCase
{
    public function testInstantiate()
    {
        $arrayCache = ArrayCache::factory(array(
            'namespace' => 'test'
        ));
        
        $this->assertInstanceOf('Squirt\Ext\Doctrine\Common\Cache\ArrayCache', $arrayCache);
    }
    
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidInstantiate()
    {
        $arrayCache = ArrayCache::factory(array(
            'namespace' => 123
        ));
    }
}
