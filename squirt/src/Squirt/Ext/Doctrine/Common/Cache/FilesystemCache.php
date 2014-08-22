<?php
namespace Squirt\Ext\Doctrine\Common\Cache;

use Doctrine\Common\Cache\FilesystemCache as DoctrineFilesystemCache;
use Squirt\Common\SquirtableInterface;
use Squirt\Ext\Doctrine\Common\Cache\SquirtableFileCacheTrait;

/**
 * Provide a Squirt-compatible instance of the Doctrine FilesystemCache
 */
class FilesystemCache extends DoctrineFilesystemCache implements SquirtableInterface
{
    use SquirtableFileCacheTrait;
}
