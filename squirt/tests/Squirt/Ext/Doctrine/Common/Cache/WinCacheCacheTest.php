<?php
namespace Squirt\Ext\Doctrine\Common\Cache;

use Squirt\Ext\Doctrine\Common\Cache\WinCacheCache;

class WinCacheCacheTest extends \PHPUnit_Framework_TestCase
{
    public function testInstantiate()
    {
        $cache = WinCacheCache::factory(array(
            'namespace' => 'test'
        ));

        $this->assertInstanceOf('Squirt\Ext\Doctrine\Common\Cache\WinCacheCache', $cache);
        $this->assertInstanceOf('Doctrine\Common\Cache\WinCacheCache', $cache);
    }
}
