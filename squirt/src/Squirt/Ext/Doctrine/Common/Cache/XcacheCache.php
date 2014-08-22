<?php
namespace Squirt\Ext\Doctrine\Common\Cache;

use Doctrine\Common\Cache\XcacheCache as DoctrineXcacheCache;
use Squirt\Common\SquirtableInterface;
use Squirt\Ext\Doctrine\Common\Cache\SquirtableCacheProviderTrait;

/**
 * Squirt wrapper for Doctrine class
 */
class XcacheCache extends DoctrineXcacheCache implements SquirtableInterface
{
    use SquirtableCacheProviderTrait;
}
