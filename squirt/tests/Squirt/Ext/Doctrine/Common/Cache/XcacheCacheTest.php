<?php
namespace Squirt\Ext\Doctrine\Common\Cache;

use Squirt\Ext\Doctrine\Common\Cache\XcacheCache;

class XcacheCacheTest extends \PHPUnit_Framework_TestCase
{
    public function testInstantiate()
    {
        $cache = XcacheCache::factory(array(
            'namespace' => 'test'
        ));

        $this->assertInstanceOf('Squirt\Ext\Doctrine\Common\Cache\XcacheCache', $cache);
        $this->assertInstanceOf('Doctrine\Common\Cache\XcacheCache', $cache);
    }
}
