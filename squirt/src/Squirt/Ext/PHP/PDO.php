<?php
namespace Squirt\Ext\PHP;

use \PDO as PhpPDO;
use Squirt\Common\SquirtableInterface;
use Squirt\Common\SquirtUtil;

/**
 * This squirt wrapper wraps the native PHP PDO class
 * in a manner that makes it configurable
 * 
 * @link http://php.net/manual/en/book.pdo.php
 */
class PDO extends PhpPDO implements SquirtableInterface
{
    /**
     * Create a new instance in a manner compatible
     * with the Squirt dependency injection
     * @param array $params
     */
    public static function factory(array $params=array())
    {
        $dsn = SquirtUtil::validateStringParam('dsn', $params);
        $username = SquirtUtil::validateStringParamWithDefault('username', $params, '');
        $password = SquirtUtil::validateStringParamWithDefault('password', $params, '');
        $options = SquirtUtil::validateArrayParamWithDefault('options', $params, array());
        
        $instance = new static($dsn, $username, $password, $options);
        
        return $instance;
    }
}