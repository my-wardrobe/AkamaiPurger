<?php

namespace Akamai\Cdn\Exception;

use \SoapFault;
use Akamai\Exception\ExceptionInterface;

/**
 * Class SoapFaultException
 *
 * @package Akamai\Cdn\Exception
 */
class SoapFaultException extends SoapFault implements ExceptionInterface
{
    // intentionally empty
}