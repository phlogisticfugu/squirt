<?php
namespace Squirt\Ext\Doctrine\Common\Cache;

use Squirt\Ext\Doctrine\Common\Cache\ZendDataCache;

class ZendDataCacheTest extends \PHPUnit_Framework_TestCase
{
    public function testInstantiate()
    {
        $cache = ZendDataCache::factory(array(
            'namespace' => 'test'
        ));

        $this->assertInstanceOf('Squirt\Ext\Doctrine\Common\Cache\ZendDataCache', $cache);
        $this->assertInstanceOf('Doctrine\Common\Cache\ZendDataCache', $cache);
    }
}
