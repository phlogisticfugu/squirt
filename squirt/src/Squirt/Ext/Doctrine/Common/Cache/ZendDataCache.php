<?php
namespace Squirt\Ext\Doctrine\Common\Cache;

use Doctrine\Common\Cache\ZendDataCache as DoctrineZendDataCache;
use Squirt\Common\SquirtableInterface;
use Squirt\Ext\Doctrine\Common\Cache\SquirtableCacheProviderTrait;

/**
 * Squirt wrapper for Doctrine class
 */
class ZendDataCache extends DoctrineZendDataCache implements SquirtableInterface
{
    use SquirtableCacheProviderTrait;
}
