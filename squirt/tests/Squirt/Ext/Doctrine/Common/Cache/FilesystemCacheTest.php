<?php
namespace Squirt\Ext\Doctrine\Common\Cache;

use Squirt\Ext\Doctrine\Common\Cache\FilesystemCache;

class FilesystemCacheTest extends \PHPUnit_Framework_TestCase
{
    public function testInstantiate()
    {
        $cache = FilesystemCache::factory(array(
            'directory' => SQUIRT_TEST_DIR . join(DIRECTORY_SEPARATOR,
                array('_testfiles','FilesystemCache')),
            'namespace' => 'test'
        ));

        $this->assertInstanceOf('Squirt\Ext\Doctrine\Common\Cache\FilesystemCache', $cache);
        $this->assertInstanceOf('Doctrine\Common\Cache\FilesystemCache', $cache);
    }
}
