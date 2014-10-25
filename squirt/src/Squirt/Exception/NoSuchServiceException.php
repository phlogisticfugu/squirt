<?php
namespace Squirt\Exception;

use Interop\Container\Exception\NotFoundException;

class NoSuchServiceException extends \RuntimeException implements NotFoundException
{}
