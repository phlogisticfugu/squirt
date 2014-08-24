<?php
namespace Squirt\Ext\Doctrine\Common\Cache;

use Squirt\Ext\Doctrine\Common\Cache\ApcCache;

class ApcCacheTest extends \PHPUnit_Framework_TestCase
{
    public function testInstantiate()
    {
        $cache = ApcCache::factory(array(
            'namespace' => 'test'
        ));

        $this->assertInstanceOf('Squirt\Ext\Doctrine\Common\Cache\ApcCache', $cache);
        $this->assertInstanceOf('Doctrine\Common\Cache\ApcCache', $cache);
    }
}
