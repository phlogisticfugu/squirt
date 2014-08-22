<?php
namespace Squirt\Ext\Doctrine\Common\Cache;

use Doctrine\Common\Cache\PhpFileCache as DoctrinePhpFileCache;
use Squirt\Common\SquirtableInterface;
use Squirt\Ext\Doctrine\Common\Cache\SquirtableFileCacheTrait;

/**
 * Provide a Squirt-compatible instance of the Doctrine PhpFileCache
 */
class PhpFileCache extends DoctrinePhpFileCache implements SquirtableInterface
{
    use SquirtableFileCacheTrait;
}
