<?php
namespace Squirt\Common;

interface SquirtableInterface
{
    /**
     * Simple static factory used to create a new instance
     * in a manner compatible with Squirt dependency injection
     * @param array $params
     */
    public static function factory(array $params=array());
}
