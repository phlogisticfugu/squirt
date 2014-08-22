<?php
namespace Squirt\Ext\Doctrine\Common\Cache;

use Doctrine\Common\Cache\WinCacheCache as DoctrineWinCacheCache;
use Squirt\Common\SquirtableInterface;
use Squirt\Ext\Doctrine\Common\Cache\SquirtableCacheProviderTrait;

/**
 * Squirt wrapper for Doctrine class
 */
class WinCacheCache extends DoctrineWinCacheCache implements SquirtableInterface
{
    use SquirtableCacheProviderTrait;
}
