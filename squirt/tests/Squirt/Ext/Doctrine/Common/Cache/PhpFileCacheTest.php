<?php
namespace Squirt\Ext\Doctrine\Common\Cache;

use Squirt\Ext\Doctrine\Common\Cache\PhpFileCache;

class PhpFileCacheTest extends \PHPUnit_Framework_TestCase
{

    private $directory;

    public function testInstantiate()
    {
        $phpFileCache = PhpFileCache::factory(array(
            'directory' => SQUIRT_TEST_DIR . join(DIRECTORY_SEPARATOR,
                array('_testfiles','PhpFileCache')),
            'namespace' => 'test'
        ));

        $this->assertInstanceOf('Squirt\Ext\Doctrine\Common\Cache\PhpFileCache', $phpFileCache);
        $this->assertInstanceOf('Doctrine\Common\Cache\PhpFileCache', $phpFileCache);

        return $phpFileCache;
    }

    /**
     * @depends testInstantiate
     * @param PhpFileCache $phpFileCache
     */
    public function testSaveFetch(PhpFileCache $phpFileCache)
    {
        $phpFileCache->flushAll();

        $value = $phpFileCache->fetch('testkey');
        $this->assertSame(false, $value, 'no data to begin with');

        $result = $phpFileCache->save('testkey', 'elephant');
        $this->assertSame(true, $result);

        $value = $phpFileCache->fetch('testkey');
        $this->assertSame('elephant', $value, 'retrieves saved data');

        $phpFileCache->flushAll();
    }
}
