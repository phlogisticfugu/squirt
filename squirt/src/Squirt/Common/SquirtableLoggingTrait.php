<?php
namespace Squirt\Common;

use \InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerAwareTrait;

/**
 * This trait implements the SquirtableLoggingInterface
 * and can be used wherever one needs to create a Squirt-compatible
 * class that also uses a Logger
 */
trait SquirtableLoggingTrait
{
    use LoggerAwareTrait;
    
    /**
     * Simple static factory used to create a new instance
     * in a manner compatible with Squirt dependency injection
     * @param array $params
     */
    public static function factory(array $params=array())
    {
        /*
         * Use late static binding to create an instance of the
         * proper concrete class
         */
        $instance = new static($params);
        
        if (array_key_exists('logger', $params)) {
            if ($params['logger'] instanceof LoggerInterface) {
                $instance->setLogger($params['logger']);
            } else {
                throw new InvalidArgumentException('Invalid logger');
            }
        }
        
        return $instance;
    }
    
    /**
     * Permit access to the logger object with which we've been configured
     * @return \Psr\Log\LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }
}
