<?php
namespace Squirt\Ext\Doctrine\Common\Cache;

use Doctrine\Common\Cache\ArrayCache as DoctrineArrayCache;
use Squirt\Common\SquirtableInterface;
use Squirt\Ext\Doctrine\Common\Cache\SquirtableCacheProviderTrait;

/**
 * Provide a Squirt-compatible instance of the Doctrine ArrayCache
 */
class ArrayCache extends DoctrineArrayCache implements SquirtableInterface
{
    use SquirtableCacheProviderTrait;
}
