<?php
namespace Squirt\Common;

use Psr\Log\LoggerAwareInterface;
use Squirt\Common\SquirtableInterface;

interface SquirtableLoggingInterface extends LoggerAwareInterface,SquirtableInterface
{}
