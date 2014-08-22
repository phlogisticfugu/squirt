<?php
namespace Squirt\Ext\Doctrine\Common\Cache;

use Doctrine\Common\Cache\ApcCache as DoctrineApcCache;
use Squirt\Common\SquirtableInterface;
use Squirt\Ext\Doctrine\Common\Cache\SquirtableCacheProviderTrait;

/**
 * Squirt wrapper for Doctrine class
 */
class ApcCache extends DoctrineApcCache implements SquirtableInterface
{
    use SquirtableCacheProviderTrait;
}
