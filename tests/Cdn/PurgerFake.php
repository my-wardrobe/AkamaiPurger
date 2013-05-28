<?php

namespace Akamai\Tests\Cdn;

use Monolog\Logger;
use Akamai\Cdn\Purger;

/**
 * Class PurgerFake
 *
 * @package Akamai\Tests\Cdn
 */
class PurgerFake extends Purger
{
    public function setLogger(Logger $logger)
    {
        $this->logger = $logger;
    }
}